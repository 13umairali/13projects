<?php

namespace App\Mail;

use App\Models\Quotation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Tells the owner a portal customer asked for a quotation, with a button that
 * deep-links to the admin quotations list filtered on portal requests.
 */
class PortalQuotationRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public Quotation $quotation;

    public $clientName;

    public $companyName;

    public function __construct(Quotation $quotation, $clientName = null, $companyName = null)
    {
        $this->quotation = $quotation;
        $this->clientName = $clientName;
        $this->companyName = $companyName;
    }

    public function build()
    {
        return $this->subject('New Quotation Request — '.$this->quotation->Ref)
            ->markdown('emails.portal_quotation_request');
    }
}
