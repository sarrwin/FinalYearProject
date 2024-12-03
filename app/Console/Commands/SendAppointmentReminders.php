<?php

namespace App\Console\Commands;
use App\Models\Appointment;
use Illuminate\Console\Command;
use App\Notifications\AppointmentReminder;
use Carbon\Carbon;
class SendAppointmentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send appointment reminders 24 hours before the appointment';

    /**
     * Execute the console command.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $appointments = Appointment::where('date', Carbon::tomorrow()->toDateString())
                                   ->where('status', 'accepted')
                                   ->get();

        foreach ($appointments as $appointment) {
            $user = $appointment->student;
            $user->notify(new AppointmentReminder($appointment));
        }

        return 0;
    }
}
