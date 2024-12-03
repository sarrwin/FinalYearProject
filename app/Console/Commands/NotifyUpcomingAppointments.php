<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\AppointmentReminder as AppointmentReminderMailable;
use Illuminate\Support\Facades\Notification;
use App\Notifications\AppointmentNotification;
use Illuminate\Console\Command;

class NotifyUpcomingAppointments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:notify-upcoming-appointments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notifications for upcoming appointments';

    /**
     * Execute the console command.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $appointments = Appointment::where('date', '=', Carbon::now()->addDay()->toDateString())
                                    ->where('status', 'accepted')
                                    ->get();

        foreach ($appointments as $appointment) {
            $user = $appointment->student;
            $user = $appointment->supervisor;

            // Send email notifications
            Mail::to($user->email)->send(new AppointmentReminderMailable($appointment));
            Mail::to($user->email)->send(new AppointmentReminderMailable($appointment));

            // Send system notifications
            Notification::send($user, new AppointmentNotification($appointment, 'You have an upcoming appointment tomorrow.'));
            Notification::send($user, new AppointmentNotification($appointment, 'You have an upcoming appointment tomorrow with a student.'));
        }

        $this->info('Notifications for upcoming appointments have been sent successfully.');
    }
}
