<?php
// app/Http/Controllers/SlotController.php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;
use App\Models\Slot;
use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
class SlotController extends Controller
{
     public function index(Request $request)
    {
        $currentDate = Carbon::now()->toDateString();
    $query = Slot::where('supervisor_id', Auth::id());
    $projects = Auth::user()->projects;
    // Apply filters based on the request
    if ($request->has('filter')) {
        $filter = $request->get('filter');
        switch ($filter) {
            case 'upcoming':
                $query->where('date', '>=', $currentDate);
                break;
            case 'past':
                $query->where('date', '<', $currentDate);
                break;
            case 'available':
                $query->where('booked', false); // Available slots
                break;
            case 'booked':
                $query->where('booked', true); // Booked slots
                break;
        }
    }

    // Default behavior: only show slots from the current date and onwards
    if (!$request->has('filter')) {
        $query->where('date', '>=', $currentDate);
    }
  // Set session flag if it doesn't exist
  if (!session()->has('google_auth_shown')) {
    session()->put('google_auth_shown', false);
    \Log::info('Session initialized: google_auth_shown set to false.');
} else {
    \Log::info('Session value: google_auth_shown = ' . session('google_auth_shown'));
}
    $slots = $query->paginate(5);

    return view('slots.index', compact('slots','projects'));
    
    }

    public function create()
{
    $projects = Auth::user()->projects; // Get projects supervised by the current user
    return view('slots.create', compact('projects')); // Pass projects to view
}

public function store(Request $request)
{
    $request->validate([
        'date' => ['required', 'date', function ($attribute, $value, $fail) {
            if (Carbon::parse($value)->isBefore(Carbon::today())) {
                $fail('The selected date must not be in the past.');
            }
        }],
        'start_time' => 'required|date_format:H:i',
        'end_time' => 'required|date_format:H:i|after:start_time',
        'meeting_details' => 'nullable|string|max:255',
        'repeat_weeks' => 'nullable|integer|min:1|max:52',
        'project_id' => 'required|exists:projects,id',
    ], [
        'date.required' => 'Please select a valid date.',
        'date.date' => 'The date must be a valid date.',
        'start_time.required' => 'Please provide a start time for the meeting.',
        'start_time.date_format' => 'Start time must follow the format HH:MM.',
        'end_time.required' => 'Please provide an end time for the meeting.',
        'end_time.date_format' => 'End time must follow the format HH:MM.',
        'end_time.after' => 'End time must be after the start time.',
        'meeting_details.required' => 'Meeting details should not exceed 255 characters.',
       
        'repeat_weeks.integer' => 'Repeat weeks must be a number between 1 and 52.',
        'repeat_weeks.min' => 'Repeat weeks must be at least 1.',
        'repeat_weeks.max' => 'Repeat weeks cannot exceed 52.',
        'project_id.required' => 'Please select a project.',
        'project_id.exists' => 'The selected project does not exist.',
    ]);

    // Your existing logic to create slots
    $supervisorId = Auth::id();
    $date = Carbon::parse($request->date);
    $startTime = $request->start_time;
    $endTime = $request->end_time;
    $meetingDetails = $request->meeting_details;
    $repeatWeeks = $request->repeat_weeks ?? 0;
    $projectId = $request->project_id;

    for ($i = 0; $i <= $repeatWeeks; $i++) {
        Slot::create([
            'supervisor_id' => $supervisorId,
            'project_id' => $projectId,
            'date' => $date->copy()->addWeeks($i),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'meeting_details' => $meetingDetails,
        ]);

        \Log::info('Creating slot with details:', [
            'supervisor_id' => $supervisorId,
            'project_id' => $projectId,
            'date' => $date->copy()->addWeeks($i),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'meeting_details' => $meetingDetails,
        ]);
    }

    return redirect()->route('slots.index')->with('success', 'Slot(s) created successfully.');
}




    

    public function destroy(Slot $slot)
    {
        if ($slot->supervisor_id != Auth::id()) {
            abort(403);
        }

        $slot->appointments()->delete();

        $slot->delete();
        return redirect()->route('slots.index')->with('success', 'Slot deleted successfully.');
    }

    public function show(User $supervisor)
    {
        $studentId = Auth::id();
        $projects = Project::whereHas('students', function ($query) use ($studentId) {
            $query->where('student_id', $studentId);
        })->get();
        if ($supervisor->role !== 'supervisor') {
            abort(404);
        }

        $slots = Slot::where('supervisor_id', $supervisor->id)->get();
        return view('students.appointments.slots', compact('supervisor', 'slots','projects'));
    }

    public function editModal(Slot $slot)
    {
        if ($slot->supervisor_id != Auth::id()) {
            abort(403);
        }
       
        return response()->json($slot); // Return slot data as JSON
    }
    

public function update(Request $request, Slot $slot)
{
    if ($slot->supervisor_id != Auth::id()) {
        abort(403);
    }
    Log::info('Update Slot Request Data:', $request->all());
    $validatedData= $request->validate([
        'date' => ['required', 'date', function ($attribute, $value, $fail) {
            if (Carbon::parse($value)->isBefore(Carbon::today())) {
                $fail('The selected date is in the past.');
            }
        }],
        'start_time' => 'required|date_format:H:i',
        'end_time' => 'required|date_format:H:i|after:start_time',
        'meeting_details' => 'nullable|string|max:255',
        'repeat_weeks' => 'nullable|integer|min:1|max:52',
        'project_id' => 'required|exists:projects,id',
    ]);
    Log::info('Validated Data for Slot Update:',  $validatedData);
    $slot->update([
        'date' => $request->date,
        'start_time' => $request->start_time,
        'end_time' => $request->end_time,
        'meeting_details' => $request->meeting_details,
        'repeat_weeks' => $request->repeat_weeks,
        'project_id' => $request->project_id,
    ]);
    Log::info('Slot Updated Successfully:', $slot->toArray());
    return redirect()->route('slots.index')->with('success', 'Slot updated successfully.');
}
}
