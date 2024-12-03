<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
class MessageNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    private $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */ public function toMail($notifiable)
{
    // Log notification being prepared
    Log::info('Preparing email notification', [
        'recipient_id' => $notifiable->id,
        'message_content' => $this->message->content,
    ]);

    return (new MailMessage)
        ->subject('New Message Notification')
        ->greeting("Hello, {$notifiable->name}")
        ->line("You have a new message from {$this->message->user->name}:")
        ->line($this->message->content)
        ->action('View Chat', url('/chatrooms/' . $this->message->chatroom_id))
        ->line('Thank you for staying connected!');
}

// Add a method to log when the notification is sent


    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
