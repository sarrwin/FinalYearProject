<?php
namespace App\Http\Controllers;
use App\Notifications\AppointmentReminder;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Slot;
use App\Models\User;
use App\Models\Project;
use App\Models\Logbook;
use App\Services\GoogleCalendarService;
use App\Notifications\AppointmentNotification;
use Illuminate\Support\Str;
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Google_Service_Calendar_EventDateTime;

class AppointmentController extends Controller
{

    protected $googleCalendarService;

    public function __construct(GoogleCalendarService $googleCalendarService)
    {
        $this->googleCalendarService = $googleCalendarService;
    }

   //---------------------------------------SUPERVISOR-------------------------------------------------------------------------------------------------- 
    
   public function upcoming($slotId = null)
{
    $appointments = Appointment::where('supervisor_id', auth()->id())
        ->with(['student', 'project', 'slot'])
        ->get();
        

    return view('supervisor.appointments.upcoming', [
        'appointments' => $appointments,
        'highlightedSlotId' => $slotId, // Pass the slotId to the view
    ]);
}

   public function upcomingSupervisor()
    {
        $appointments = Appointment::where('status', 'accepted')
            ->where(function ($query) {
                $query->whereHas('slot', function ($query) {
                    $query->where('supervisor_id', Auth::id())
                          ->where('date', '>=', now());
                })
                ->orWhere(function ($query) {
                    $query->whereNull('slot_id')
                          ->where('date', '>=', now())
                          ->where('supervisor_id', Auth::id());
                });
            })
            ->get();
           // $this->addAppointmentsToGoogleCalendar($appointments);
        return view('supervisor.appointments.upcoming', compact('appointments'));
    }   
    public function pastSupervisor()
    {
        $appointments = Appointment::where(function ($query) {
                $query->whereHas('slot', function ($query) {
                    $query->where('supervisor_id', Auth::id())
                          ->where('date', '<', now());
                })
                ->orWhere(function ($query) {
                    $query->whereNull('slot_id')
                          ->where('date', '<', now())
                          ->where('supervisor_id', Auth::id());
                });
            })
            ->orWhere(function ($query) {
                $query->where('supervisor_id', Auth::id()) // Ensure it applies to current user's appointments
                    ->where(function ($query) {
                        $query->where('status', 'completed')
                            ->orWhere('status', 'cancelled')
                            ->orWhere('status', 'declined');
                    });
            })
            ->get();
    
        return view('supervisor.appointments.past', compact('appointments'));
    }


    public function cancelWithReason(Request $request, Appointment $appointment)
{
    \Log::info('Controller hit:', [
        'appointment_id' => $appointment->id,
        'request_data' => $request->all(),
    ]);
    $request->validate([
        'reason' => 'required|string|max:255',
    ]);
    \Log::info('Validation passed for cancel reason', ['reason' => $request->reason]);

    \Log::info('Cancelling appointment with ID: ' . $appointment->id, ['reason' => $request->reason]);

    $appointment->update([
        'status' => 'cancelled',
        'request_reason' => $request->reason,
    ]);

    \Log::info('Appointment status updated in database', [
        'appointment_id' => $appointment->id,
        'status' => $appointment->status,
        'reason' => $appointment->request_reason,
    ]);

    if ($appointment->Gmeet_link) {
        \Log::info('Google Meet link found, attempting to delete from Google Calendar', [
            'Gmeet_link' => $appointment->Gmeet_link,
        ]);
        $this->deleteGoogleCalendarEvent($appointment);
    } else {
        \Log::info('No Google Meet link found, skipping Google Calendar deletion');
    }

    // Notify all group members of the cancellation
    $groupMembers = $appointment->project->students;
    foreach ($groupMembers as $groupMember) {
        $groupMember->notify(new AppointmentReminder($appointment));
        \Log::info('Group member notified of cancellation', ['student_id' => $groupMember->id, 'appointment_id' => $appointment->id]);
    }

    return redirect()->route('supervisor.appointments.upcoming')->with('success', 'Appointment canceled successfully and removed from Google Calendar.');
}



protected function deleteGoogleCalendarEvent(Appointment $appointment)
{
    $student = $appointment->student;

    if (!$appointment->google_event_id) {
        \Log::warning('No Google Calendar event ID found for appointment', ['appointment_id' => $appointment->id]);
        return false;
    }

    $client = $this->initializeGoogleClient($student);
    $service = new Google_Service_Calendar($client);

    try {
        $service->events->delete('primary', $appointment->google_event_id);
        \Log::info('Google Calendar event deleted successfully', [
            'event_id' => $appointment->google_event_id,
            'user_id' => $student->id
        ]);
        return true;
    } catch (\Exception $e) {
        \Log::error('Error deleting Google Calendar event:', [
            'event_id' => $appointment->google_event_id,
            'user_id' => $student->id,
            'error' => $e->getMessage(),
        ]);
        return false;
    }
}
public function updateMeetingDetails(Request $request, Appointment $appointment)
{
    try {
        $request->validate([
            'meeting_details' => 'required|string|max:255',
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        $appointment->update([
            'meeting_details' => $request->meeting_details,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        // Update Google Calendar Event if the Google event ID is available
        if ($appointment->google_event_id) {
            $this->updateGoogleCalendarEvent($appointment);
        }
    } catch (\Exception $e) {
        \Log::error('Error updating appointment: ' . $e->getMessage());
        return back()->withErrors(['error' => 'Unable to update appointment.']);
    }

   

    // Fallback (if no role matches)
    return back()->with('success', 'Meeting details updated successfully.');
}


// Method to update the event on Google Calendar
protected function updateGoogleCalendarEvent(Appointment $appointment)
{
    $student = $appointment->student;
    $supervisor = $appointment->supervisor;

    // Initialize clients for student and supervisor
    $studentClient = $this->initializeGoogleClient($student);
    $supervisorClient = $this->initializeGoogleClient($supervisor);

    // Format start and end times
    $startDateTime = \Carbon\Carbon::parse("{$appointment->date} {$appointment->start_time}")->toRfc3339String();
    $endDateTime = \Carbon\Carbon::parse("{$appointment->date} {$appointment->end_time}")->toRfc3339String();

    // Update Google Calendar event details
    $event = new Google_Service_Calendar_Event([
        'summary' => 'Updated Appointment with Supervisor',
        'location' => 'Online',
        'description' => $appointment->meeting_details,
        'start' => [
            'dateTime' => $startDateTime,
            'timeZone' => 'Asia/Kuala_Lumpur',
        ],
        'end' => [
            'dateTime' => $endDateTime,
            'timeZone' => 'Asia/Kuala_Lumpur',
        ],
        'attendees' => [
            ['email' => $student->email], // Add student as an attendee
            ['email' => $supervisor->email], // Add supervisor as an attendee
        ],
    ]);

    // Update for Student
    if ($studentClient) {
        try {
            $studentService = new Google_Service_Calendar($studentClient);
            $studentService->events->update('primary', $appointment->google_event_id, $event, [
                'sendUpdates' => 'all',
            ]);
            \Log::info('Google Calendar event updated successfully for student', ['event_id' => $appointment->google_event_id, 'user_id' => $student->id]);
        } catch (\Exception $e) {
            \Log::error("Error updating event for student {$student->id}: {$e->getMessage()}");
        }
    } else {
        \Log::warning("Student {$student->id} needs to reauthenticate to update Google Calendar.");
    }

    // Update for Supervisor
    if ($supervisorClient) {
        try {
            $supervisorService = new Google_Service_Calendar($supervisorClient);
            $supervisorService->events->update('primary', $appointment->google_event_id, $event, [
                'sendUpdates' => 'all',
            ]);
            \Log::info('Google Calendar event updated successfully for supervisor', ['event_id' => $appointment->google_event_id, 'user_id' => $supervisor->id]);
        } catch (\Exception $e) {
            \Log::error("Error updating event for supervisor {$supervisor->id}: {$e->getMessage()}");
        }
    } else {
        \Log::warning("Supervisor {$supervisor->id} needs to reauthenticate to update Google Calendar.");
    }
}














    

    public function manage()
    {
        $appointments = Appointment::where('status', 'pending')
        ->where(function ($query) {
            $query->whereHas('slot', function ($query) {
                $query->where('supervisor_id', Auth::id());
            })
            ->orWhere('supervisor_id', Auth::id());
        })
        ->get();

    return view('supervisor.appointments.manage', compact('appointments'));
    }

    public function updateStatus(Request $request, Appointment $appointment)
    {
        $request->validate([
            'status' => 'required|in:accepted,declined',
            'decline_reason' => 'nullable|string|max:255',
        ]);
    
        // Update the appointment status
        $appointment->update([
            'status' => $request->status,
            'decline_reason' => $request->decline_reason ?? null,
        ]);
    
        // Notify the student
        $student = User::find($appointment->student_id);
        if ($request->status === 'accepted') {
            $student->notify(new AppointmentReminder($appointment));
        } else {
            $message = 'Your appointment request for ' . $appointment->date . ' at ' . $appointment->start_time;
            $message .= $request->status === 'declined'
                ? ' has been declined. Reason: ' . $request->decline_reason
                : ' has been cancelled.';
            $student->notify(new AppointmentNotification($appointment, $message));
        }
    
        if ($request->status === 'accepted') {
            // Fetch the related slot
            $slot = $appointment->slot;
    
            // Fetch project members
            $project = $appointment->project;
            $groupMembers = $project ? $project->students : collect();
    
            // Create Google Calendar Event
            $this->createGoogleCalendarEvent($student, $slot->supervisor, $slot, $groupMembers);
        }
    
        return redirect()->route('supervisor.appointments.manage')->with('success', 'Appointment status updated.');
    }
    


    
    
//---------------------------------STUDENT------------------------------------------------------------------------------------------






public function indexNormal(){
    return view('students.appointments.index');
}
public function bookSlot(Slot $slot)
{
    if ($slot->booked) {
        return redirect()->route('students.appointments.upcoming')->with('error', 'This slot is already booked.');
    }

    // Create appointment for the booking student
    $appointment = Appointment::create([
        'student_id' => Auth::id(),
        'slot_id' => $slot->id,
        'supervisor_id' => $slot->supervisor_id,
        'project_id' => $slot->project_id,
        'date' => $slot->date,
        'start_time' => $slot->start_time,
        'end_time' => $slot->end_time,
        'status' => 'accepted',
    ]);

    \Log::info('Appointment created for booking student.', ['appointment_id' => $appointment->id, 'student_id' => Auth::id()]);

    // Mark slot as booked
    $slot->update(['booked' => true]);
    \Log::info('Slot marked as booked.', ['slot_id' => $slot->id]);

    // Retrieve group members, excluding the booking student
      // Retrieve all group members for the same project
      $project = Project::find($slot->project_id);
      if (!$project) {
          \Log::error('Project could not be found for slot.', ['slot_id' => $slot->id, 'project_id' => $slot->project_id]);
          return redirect()->route('students.appointments.upcoming')->with('error', 'Project not found.');
      }
    $groupMembers = $project->students;

    \Log::info('Group members retrieved.', ['group_member_count' => $groupMembers->count(), 'project_id' => $slot->project_id]);

    // Send Google Calendar invitations for the booking student, supervisor, and group members
    $student = Auth::user();
    $supervisor = $slot->supervisor;

    if ($student->google_token || $supervisor->google_token) {
        $this->createGoogleCalendarEvent($student, $supervisor, $slot, $groupMembers);
    } else {
        \Log::warning('Google token missing for either student or supervisor. Event creation skipped.');
    }

    $student->notify(new AppointmentReminder($appointment));
    \Log::info('Booking student notified of the appointment.', ['student_id' => $student->id]);
    $upcomingAppointments = Appointment::where(function ($query) {
        $query->where('student_id', Auth::id())
              ->orWhereHas('project.students', function ($studentQuery) {
                  $studentQuery->where('users.id', Auth::id());
              });
    })
    ->whereHas('slot', function ($slotQuery) {
        $slotQuery->where('date', '>=', now());
    })
    ->orderBy('date', 'asc')
    ->orderBy('start_time', 'asc')
    ->get();
    \Log::info('Total upcoming appointments:', ['count' => $upcomingAppointments->count()]);
// Find the index of the newly booked appointment
$index = $upcomingAppointments->search(function ($item) use ($appointment) {
    return $item->id === $appointment->id;
});

if ($index === false) {
    \Log::error('New appointment not found in upcoming list.', ['appointment_id' => $appointment->id]);
    return redirect()->route('students.appointments.upcoming')->with('error', 'Unable to locate the booked appointment.');
}

// Calculate the page number
$perPage = 10; // Matches the pagination limit
$page = (int) ceil(($index + 1) / $perPage);

\Log::info('Index of new appointment:', ['index' => $index]);
\Log::info('Calculated page number:', ['page' => $page]);

return redirect()->route('students.appointments.upcoming', ['page' => $page])
                 ->with('success', 'Appointment booked successfully and synced with Google Calendar.');
}


protected function createGoogleCalendarEvent($student, $supervisor, $slot, $groupMembers)
{
    // Initialize clients
    $studentClient = $this->initializeGoogleClient($student);
   $supervisorClient = $this->initializeGoogleClient($supervisor);

    // Log the values for debugging
    \Log::info('Slot Date:', ['date' => $slot->date]);
    \Log::info('Slot Start Time:', ['start_time' => $slot->start_time]);
    \Log::info('Slot End Time:', ['end_time' => $slot->end_time]);

    // Format start and end times correctly
    $dateOnly = explode(' ', trim($slot->date))[0];
    
    // Use the time part from $slot->start_time and $slot->end_time directly
    $startTimeOnly = explode(' ', trim($slot->start_time))[1] ?? $slot->start_time;
    $endTimeOnly = explode(' ', trim($slot->end_time))[1] ?? $slot->end_time;

    // Format start and end times by combining date and time correctly
    $startDateTime = \Carbon\Carbon::parse("{$dateOnly} {$startTimeOnly}")->toRfc3339String();
    $endDateTime = \Carbon\Carbon::parse("{$dateOnly} {$endTimeOnly}")->toRfc3339String();

    // Add attendees including group members
    $attendees = [
        ['email' => $student->email],
        ['email' => $supervisor->email]
    ];
    \Log::info('Added supervisor to Google Calendar event attendees.', ['supervisor_id' => $supervisor->id, 'email' => $supervisor->email]);
    foreach ($groupMembers as $groupMember) {
        if ($groupMember->google_token) {
            $attendees[] = ['email' => $groupMember->email];
            \Log::info('Added group member to Google Calendar event attendees.', ['group_member_id' => $groupMember->id, 'email' => $groupMember->email]);
        }
        // } else {
        //     \Log::warning('Google token missing for group member. Skipping invitation.', ['group_member_id' => $groupMember->id]);
        // }
    }

    $event = new Google_Service_Calendar_Event([
        'summary' => 'Appointment with Supervisor',
        'location' => 'Online',
        'description' => $slot->meeting_details,
        'start' => [
            'dateTime' => $startDateTime,
            'timeZone' => 'Asia/Kuala_Lumpur',
        ],
        'end' => [
            'dateTime' => $endDateTime,
            'timeZone' => 'Asia/Kuala_Lumpur',
        ],
        'attendees' => $attendees,
        'conferenceData' => [
            'createRequest' => [
                'conferenceSolutionKey' => ['type' => 'hangoutsMeet'],
                'requestId' => 'meet-' . uniqid(),
            ],
        ],
    ]);

    try {
        // Create the event on the student’s calendar
        $studentService = new Google_Service_Calendar($studentClient);
        $createdEvent = $studentService->events->insert('primary', $event, ['conferenceDataVersion' => 1,'sendUpdates' => 'all']);
        $googleMeetLink = $createdEvent->getHangoutLink();
        $eventId = $createdEvent->getId();
        
        $appointment = Appointment::where('slot_id', $slot->id)->first();
        if ($appointment) {
            $appointment->Gmeet_link = $googleMeetLink;
            $appointment->google_event_id = $eventId;
            $appointment->save();
            \Log::info('Google Meet link saved to appointment.', ['appointment_id' => $appointment->id]);
        }

        // Sync to supervisor’s calendar
    $supervisorService = new Google_Service_Calendar($supervisorClient);
     $supervisorService->events->insert('primary', $createdEvent, ['conferenceDataVersion' => 1]);
        
        \Log::info('Google Calendar event created successfully for student, supervisor, and group members.', ['event_id' => $eventId]);
    } catch (\Exception $e) {
        \Log::error('Error creating Google Calendar event:', ['error' => $e->getMessage()]);
    }
}






/**
 * Initialize Google Client for a user and refresh token if needed.
 */
protected function initializeGoogleClient($user, $forceRefresh = false, $retryCount = 0)
{
    $MAX_RETRY_ATTEMPTS = 1; // Set a limit on how many times we want to retry

    $client = new Google_Client();
    $client->setAccessToken($user->google_token);

    // If the token is expired or if we need to force a refresh
    if ($forceRefresh || $client->isAccessTokenExpired()) {
        if ($retryCount >= $MAX_RETRY_ATTEMPTS) {
            \Log::error("Max retry attempts reached for user {$user->id}. Token refresh failed.");
            throw new \Exception("Unable to refresh token after multiple attempts.");
        }

        try {
            \Log::info('Refreshing token for user', ['user_id' => $user->id]);
            $client->refreshToken($user->google_refresh_token);
            $newToken = $client->getAccessToken();

            // Check if the new token is valid before saving
            if (isset($newToken['access_token'])) {
                $user->google_token = $newToken['access_token'];
                $user->save();
                \Log::info('Token refreshed and saved', ['new_token' => $newToken]);
            } else {
                \Log::warning("Failed to retrieve valid access token for user {$user->id}. Retrying...");
                // Recursive call with incremented retryCount
                return $this->initializeGoogleClient($user, $forceRefresh, $retryCount + 1);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to refresh token:', ['error' => $e->getMessage()]);
            throw $e; // Re-throw the exception to handle it in the calling code
        }
    }

    return $client;
}


protected function notifyGroup($appointment, $student, $supervisor, $groupMembers)
{
    // Notify supervisor and booking student
    $student->notify(new AppointmentReminder($appointment));
    $supervisor->notify(new AppointmentReminder($appointment));

    // Notify each group member
    foreach ($groupMembers as $member) {
        $member->notify(new AppointmentReminder($appointment));
    }
}










    
public function requestOwnTime(Request $request)
{
    $request->validate([
        'date' => 'required|date',
        'start_time' => 'required',
        'end_time' => 'required',
        'request_reason' => 'required|string|max:255',
        'supervisor_id' => 'required|exists:users,id',
        'is_project_meeting' => 'required|boolean', // Indicate if this is a project meeting
        'project_id' => 'nullable|exists:projects,id', // Optional for project meetings
    ]);

    // Ensure project_id is provided if it's a project meeting
    if ($request->is_project_meeting && !$request->project_id) {
        return redirect()->back()->withErrors(['project_id' => 'Please select a project for a project meeting.']);
    }

    // Extract and format start and end times
    $dateOnly = $request->date;
    $startTimeOnly = explode(' ', trim($request->start_time))[1] ?? $request->start_time;
    $endTimeOnly = explode(' ', trim($request->end_time))[1] ?? $request->end_time;

    $startDateTime = \Carbon\Carbon::parse("{$dateOnly} {$startTimeOnly}");
    $endDateTime = \Carbon\Carbon::parse("{$dateOnly} {$endTimeOnly}");

    // Validate that the start time is earlier than the end time
    if ($startDateTime->gte($endDateTime)) {
        return redirect()->back()->withErrors(['error' => 'The start time must be earlier than the end time.']);
    }

    // Prevent overlapping appointments
    $existingSlot = Slot::where('supervisor_id', $request->supervisor_id)
        ->where('date', $request->date)
        ->where(function ($query) use ($startTimeOnly, $endTimeOnly) {
            $query->whereBetween('start_time', [$startTimeOnly, $endTimeOnly])
                  ->orWhereBetween('end_time', [$startTimeOnly, $endTimeOnly]);
        })
        ->first();

    if ($existingSlot) {
        return redirect()->back()->withErrors(['error' => 'The requested time overlaps with an existing slot.']);
    }

    // Create the slot
    $slot = Slot::create([
        'supervisor_id' => $request->supervisor_id,
        'date' => $request->date,
        'start_time' => $startTimeOnly,
        'end_time' => $endTimeOnly,
        'meeting_details' => $request->request_reason,
        'booked' => null,
    ]);

    // Create the appointment request
    $appointment = Appointment::create([
        'student_id' => Auth::id(),
        'slot_id' => $slot->id,
        'supervisor_id' => $request->supervisor_id,
        'project_id' => $request->is_project_meeting ? $request->project_id : null,
        'date' => $request->date,
        'start_time' => $startTimeOnly,
        'end_time' => $endTimeOnly,
        'status' => 'pending',
        'request_reason' => $request->request_reason,
        'meeting_details' => $request->is_project_meeting 
            ? "Project Meeting: " . Project::find($request->project_id)->title 
            : "General Meeting: " . $request->request_reason,
    ]);

    // Notify the supervisor of the new appointment request
    $supervisor = User::find($request->supervisor_id);
    $supervisor->notify(new AppointmentNotification($appointment, 'A new appointment request has been made by a student.'));

    return redirect()->back()->with('success', 'Custom request sent successfully.');
}



    public function upcomingStudent()
    {
        \Log::info('Fetching upcoming appointments for student', ['student_id' => Auth::id()]);
    
        $appointments = Appointment::where(function ($query) {
                // Fetch appointments booked by the current student
                $query->where('student_id', Auth::id())
                      ->where(function ($statusQuery) {
                          $statusQuery->where('status', 'accepted')
                                      ->orWhere('status', 'pending');
                      });
            })
            ->orWhere(function ($query) {
                // Fetch appointments for group members with the same project_id
                $query->whereHas('project', function ($projectQuery) {
                    $projectQuery->whereIn('id', function ($subQuery) {
                        $subQuery->select('project_id')
                                 ->from('project_student')
                                 ->where('student_id', Auth::id());
                    });
                })
                ->where(function ($statusQuery) {
                    $statusQuery->where('status', 'accepted')
                                ->orWhere('status', 'pending');
                });
            })
            ->where(function ($query) {
                // Filter appointments for upcoming dates
                $query->whereHas('slot', function ($slotQuery) {
                    $slotQuery->where('date', '>=', now());
                })
                ->orWhere('date', '>=', now());
            })
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->with('slot.supervisor', 'supervisor', 'project')
            ->paginate(10);
    
        \Log::info('Upcoming appointments retrieved', ['appointments_count' => $appointments->count()]);
    
        // Log each appointment with details
        foreach ($appointments as $appointment) {
            \Log::info('Appointment details', [
                'appointment_id' => $appointment->id,
                'student_id' => $appointment->student_id,
                'project_id' => $appointment->project_id,
                'date' => $appointment->date,
                'start_time' => $appointment->start_time,
                'end_time' => $appointment->end_time,
                'Gmeet_link' => $appointment->Gmeet_link
            ]);
        }
    
        return view('students.appointments.upcoming', compact('appointments'));
    }
    
    public function showRequestForm()
    {
        $studentId = Auth::id();
    
        // Fetch projects associated with the student
        $projects = Project::whereHas('students', function ($query) use ($studentId) {
            $query->where('id', $studentId);
        })->get();
    
        // Fetch supervisors (assuming a relationship or Supervisor model exists)

    
        // Pass data to the Blade view
        return view('students.appointments.slots', compact('projects'));
    } 




public function pastStudent()
{
    $appointments = Appointment::where('student_id', Auth::id()) // Filter by current user's student_id
        ->where(function ($query) {
            $query->whereHas('slot', function ($query) {
                $query->where('date', '<', now());
            })
            ->orWhere(function ($query) {
                $query->where('date', '<', now())
                      ->whereNull('slot_id');
            });
        })
        ->orWhere(function ($query) {
            $query->where('student_id', Auth::id()) // Ensure it applies to current user's appointments
                ->where(function ($query) {
                    $query->where('status', 'completed')
                        ->orWhere('status', 'cancelled');
                });
        })
        ->with('slot.supervisor', 'supervisor')
        ->get();

    return view('students.appointments.past', compact('appointments'));
}

    

   

protected function deleteEventFromUserCalendar($user, $eventId)
{
    if (!$user || !$user->google_token) {
        \Log::warning('Google token not found for user', ['user_id' => $user->id]);
        return false;
    }

    $client = new Google_Client();
    $client->setAccessToken($user->google_token);

    if ($client->isAccessTokenExpired()) {
        $client->refreshToken($user->google_refresh_token);
        $user->google_token = $client->getAccessToken()['access_token'];
        $user->save();
    }

    $service = new Google_Service_Calendar($client);

    try {
        $service->events->delete('primary', $eventId);
        \Log::info('Google Calendar event deleted successfully', [
            'event_id' => $eventId,
            'user_id' => $user->id
        ]);
        return true;
    } catch (\Google_Service_Exception $e) {
        if ($e->getCode() == 401) {
            \Log::error('Invalid credentials for Google Calendar event deletion, prompting reauthentication.', [
                'event_id' => $eventId,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        } else {
            \Log::error('Error deleting Google Calendar event', [
                'event_id' => $eventId,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    return false;
}










}


























