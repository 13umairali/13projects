<?php

namespace App\Notifications;

use App\Models\Quotation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Records a quotation requested from the client portal for admin users
 * (database channel → admin bell), mirroring NewOnlineOrderNotification.
 * The owner is emailed separately so every admin doesn't get their own copy.
 */
class NewPortalQuotationRequestNotification extends Notification
{
    use Queueable;

    protected Quotation $quotation;

    protected ?string $clientName;

    public function __construct(Quotation $quotation, ?string $clientName = null)
    {
        $this->quotation = $quotation;
        $this->clientName = $clientName;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'portal_quotation_request',
            'quotation_id' => $this->quotation->id,
            'ref' => $this->quotation->Ref,
            'client_id' => $this->quotation->client_id,
            'client_name' => $this->clientName,
            'message' => __('messages.NewPortalQuotationRequest').': '.$this->quotation->Ref,
        ];
    }
}
