@component('mail::message')
# {{ __('New Quotation Request') }}

@php
  // Markdown collapses single newlines into one paragraph, so the details go
  // out as a real list (see emails/admin_new_order.blade.php).
  $requestedAt = trim(
      ($quotation->date instanceof \Carbon\CarbonInterface ? $quotation->date->format('Y-m-d') : (string) $quotation->date)
      .' '.(string) $quotation->time
  );
@endphp

- **{{ __('Reference') }}:** {{ $quotation->Ref }}
- **{{ __('Date') }}:** {{ $requestedAt }}
- **{{ __('Customer') }}:** {{ $clientName ?: '—' }}

**{{ __('Details') }}:**

<span>{!! nl2br(e($quotation->notes)) !!}</span>

@component('mail::button', ['url' => url('/quotations?source=portal&statut=pending')])
{{ __('Review Request') }}
@endcomponent

{{ $companyName ?: config('app.name') }}
@endcomponent
