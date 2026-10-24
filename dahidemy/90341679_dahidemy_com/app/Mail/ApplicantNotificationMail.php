<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApplicantNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  string[]  $lines  Body paragraphs, rendered in order.
     */
    public function __construct(
        public string $recipientName,
        public string $subjectLine,
        public array $lines,
        public ?string $schoolName = null,
    ) {}

    public function build(): self
    {
        return $this->subject($this->subjectLine)
            ->view('emails.applicant-notification')
            ->with([
                'recipientName' => $this->recipientName,
                'lines' => $this->lines,
                // Passed in explicitly by the caller rather than read via
                // get_option(), which silently resolves to nothing outside
                // an authenticated request (see ApplicantNotificationService).
                'schoolName' => $this->schoolName ?: config('app.name'),
            ]);
    }
}
