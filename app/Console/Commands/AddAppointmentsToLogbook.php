<?php

namespace App\Console\Commands;

use App\Models\LogbookEntry;
use Illuminate\Console\Command;
use App\Models\Appointment;
use App\Models\Logbook;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AddAppointmentsToLogbook extends Command
{
    protected $signature = 'appointments:add-to-logbook';
    protected $description = 'Add past appointments to logbook';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        Log::info('AddAppointmentsToLogbook command started.');

        $appointments = Appointment::where('status', 'accepted')
            ->where(function ($query) {
                $query->whereHas('slot', function ($query) {
                    $query->where('date', '<=', Carbon::now());
                })
                ->orWhere(function ($query) {
                    $query->where('date', '<=', Carbon::now())
                          ->whereNull('slot_id');
                });
            })
            ->get();

        Log::info('Appointments found:', ['count' => $appointments->count()]);

        foreach ($appointments as $appointment) {
            // Fetch the student's project
            $project = Project::whereHas('students', function ($query) use ($appointment) {
                $query->where('users.id', $appointment->student_id);
            })->first();

            if (!$project) {
                Log::warning('No project found for student', ['student_id' => $appointment->student_id]);
                continue;
            }
            $logbook = Logbook::firstOrCreate(['project_id' => $project->id]);


            $entry = 'Meeting with Supervisor ' . $appointment->supervisor->name;

            Log::info('Processing appointment:', ['appointment_id' => $appointment->id]);

            LogbookEntry::create([
                'logbook_id' => $logbook->id,
                'student_id' => $appointment->student_id,
                'project_id' => $project->id, // Use the fetched project ID
                'activity' => $entry,
                'activity_date' => $appointment->slot ? $appointment->slot->date : $appointment->date,
                'verified' => false,
            ]);

            $appointment->update(['status' => 'completed']);

            Log::info('Appointment added to logbook and marked as completed.', ['appointment_id' => $appointment->id]);
        }

        $this->info('Appointments added to logbook successfully.');
        Log::info('AddAppointmentsToLogbook command finished.');
    }
}
