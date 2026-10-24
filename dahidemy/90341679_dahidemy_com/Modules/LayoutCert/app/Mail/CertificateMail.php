<?php

namespace Modules\LayoutCert\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CertificateMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $certificateTypeLabel,
        public string $studentName,
        public string $pdfContent,
        public string $fileName,
    ) {}

    public function build()
    {
        return $this->subject($this->certificateTypeLabel.' — '.$this->studentName)
            ->markdown('emails.certificate.issued')
            ->with([
                'certificateTypeLabel' => $this->certificateTypeLabel,
                'studentName' => $this->studentName,
            ])
            ->attachData($this->pdfContent, $this->fileName, [
                'mime' => 'application/pdf',
            ]);
    }
}
