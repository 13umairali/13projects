<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdmitCardMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $studentName,
        public string $pdfContent,
        public string $fileName,
    ) {}

    public function build()
    {
        return $this->subject('Your Exam Admit Card')
            ->markdown('emails.admit-card.issued')
            ->with([
                'studentName' => $this->studentName,
            ])
            ->attachData($this->pdfContent, $this->fileName, [
                'mime' => 'application/pdf',
            ]);
    }
}
