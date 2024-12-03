<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Appointment;

class AppointmentNotification extends Notification
{
    use Queueable;

    public $appointment;
    public $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(Appointment $appointment, $message)
    {
        $this->appointment = $appointment;
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line($this->message)
                    ->line('Date: ' . $this->appointment->date)
                    ->line('Time: ' . $this->appointment->start_time . ' - ' . $this->appointment->end_time)
                    ->action('View Appointment', url('/appointments/' . $this->appointment->id))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        return [
            'message' => $this->message,
            'appointment_id' => $this->appointment->id,
            'date' => $this->appointment->date,
            'start_time' => $this->appointment->start_time,
            'end_time' => $this->appointment->end_time,
            'status' => $this->appointment->status,
            'request_reason' => $this->appointment->request_reason,
            'supervisor' => $this->appointment->supervisor->name,
            'student' => $this->appointment->student->name,
        ];

        
    }
}
