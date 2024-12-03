<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FileApprovalNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $details;

    /**
     * Create a new message instance.
     *
     * @param array $details
     * @return void
     */
    public function __construct($details)
    {
        $this->details = $details;
        \Log::info('Mailable Details:', $this->details);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('File Approval Notification')
                    ->view('emails.file_approval') // Pass the view
                    ->with(['details' => $this->details]); // Pass details to the view

                    
    }
}
