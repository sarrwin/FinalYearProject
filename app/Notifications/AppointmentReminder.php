<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Appointment;

class AppointmentReminder extends Notification
{
    use Queueable;

    protected $appointment;

    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $mailMessage = new MailMessage();

        switch ($this->appointment->status) {
            case 'pending':
                $mailMessage->line('You have a new appointment request.')
                    ->line('Date: ' . $this->appointment->date)
                    ->line('Time: ' . $this->appointment->start_time . ' - ' . $this->appointment->end_time)
                    ->line('Request Reason: ' . $this->appointment->request_reason)
                    ->action('View Appointment', url('/appointments/' . $this->appointment->id))
                    ->line('Thank you for using our application!');
                break;

            case 'accepted':
                $mailMessage->line('Your appointment request has been accepted.')
                    ->line('Date: ' . $this->appointment->date)
                    ->line('Time: ' . $this->appointment->start_time . ' - ' . $this->appointment->end_time)
                    ->line('Meeting Details: ' . $this->appointment->meeting_details)
                    ->action('View Appointment', url('/appointments/' . $this->appointment->id))
                    ->line('Thank you for using our application!');
                break;

            case 'declined':
                $mailMessage->line('Your appointment request has been declined.')
                    ->line('Date: ' . $this->appointment->date)
                    ->line('Time: ' . $this->appointment->start_time . ' - ' . $this->appointment->end_time)
                    ->line('Decline Reason: ' . $this->appointment->request_reason)
                    ->line('We apologize for any inconvenience caused.')
                    ->line('Thank you for using our application!');
                break;

            case 'cancelled':
                $mailMessage->line('Your appointment has been cancelled.')
                    ->line('Date: ' . $this->appointment->date)
                    ->line('Time: ' . $this->appointment->start_time . ' - ' . $this->appointment->end_time)
                    ->line('Cancellation Reason: ' . $this->appointment->request_reason)
                    ->line('We apologize for any inconvenience caused.')
                    ->line('Thank you for using our application!');
                break;

            default:
                $mailMessage->line('You have an upcoming appointment.')
                    ->line('Date: ' . $this->appointment->date)
                    ->line('Time: ' . $this->appointment->start_time . ' - ' . $this->appointment->end_time)
                    ->line('Meeting Details: ' . $this->appointment->meeting_details)
                    ->action('View Appointment', url('/appointments/' . $this->appointment->id))
                    ->line('Thank you for using our application!');
                break;
        }

        return $mailMessage;
    }

    public function toDatabase($notifiable)
    {
        return [
            'appointment_id' => $this->appointment->id,
            'date' => $this->appointment->date,
            'start_time' => $this->appointment->start_time,
            'end_time' => $this->appointment->end_time,
            'meeting_details' => $this->appointment->meeting_details,
            'status' => $this->appointment->status,
            'request_reason' => $this->appointment->request_reason,
        ];
    }
}
