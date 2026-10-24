<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Academic\Models\Notice;

class NoticeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Notice $notice) {}

    public function build()
    {
        return $this->subject($this->notice->title)
            ->markdown('emails.notice.issued')
            ->with([
                'title' => $this->notice->title,
                'body' => $this->notice->renderedBody(),
                'type' => $this->notice->type,
            ]);
    }
}
