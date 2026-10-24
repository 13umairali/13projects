<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('central.XpayPayTitle') }} — {{ $appName }}</title>
    <link rel="icon" href="{{ asset('images/super/settings/favicon.ico') }}">
    <link href="{{ asset('assets_super/css/plus-jakarta-sans.css') }}" rel="stylesheet">
    <link href="{{ asset('assets_super/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets_super/css/bootstrap-icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets_super/css/central-shared.css') }}" rel="stylesheet">
    <link href="{{ asset('assets_super/css/checkout.css') }}" rel="stylesheet">
    <style>
        #xpay-element { min-height: 120px; }
        #xpay-element:empty::after {
            content: '{{ __('central.XpayLoadingForm') }}';
            display: block; text-align: center; color: #6b7280; padding: 2rem 0; font-size: .9rem;
        }
        .xpay-error { display: none; }
        .xpay-error.show { display: flex; }
    </style>
    <script defer src="{{ $element['sdk_url'] }}"></script>
</head>
<body>
    <div class="checkout-container">
        <div class="mb-4">
            <a href="{{ $cancelUrl }}" class="back-link">
                <i class="bi bi-arrow-left"></i> {{ __('central.XpayBackToCheckout') }}
            </a>
        </div>

        <div class="row g-4">
            <div class="col-lg-5 order-lg-2">
                <div class="checkout-card sticky-sidebar-sm">
                    <div class="checkout-card-header">
                        <i class="bi bi-receipt me-2 text-muted"></i>{{ __('central.OrderSummary') }}
                    </div>
                    <div class="checkout-card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">{{ __('central.Plan') }}</span>
                            <span class="fw-bold">{{ $planName }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">{{ __('central.BillingCycle') }}</span>
                            <span class="fw-bold">{{ ucfirst((string) $cycle) }}</span>
                        </div>
                        <hr class="checkout-separator">
                        <div class="d-flex justify-content-between">
                            <span class="checkout-total-label">{{ __('central.Total') }}</span>
                            <span class="checkout-total-value">{{ number_format($amount, 2) }} {{ $chargeCurrency }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7 order-lg-1">
                <form id="xpayForm" onsubmit="return false;">
                    <div class="checkout-card mb-4">
                        <div class="checkout-card-header">
                            <i class="bi bi-credit-card me-2 text-muted"></i>{{ __('central.XpayCardDetails') }}
                        </div>
                        <div class="checkout-card-body">
                            <div id="xpay-element"></div>
                            <div class="alert alert-danger d-flex align-items-center gap-2 mt-3 mb-0 checkout-alert xpay-error" id="xpayError" role="alert">
                                <i class="bi bi-x-circle-fill"></i>
                                <span id="xpayErrorText"></span>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn-pay" id="payBtn" disabled>
                        <i class="bi bi-lock-fill me-2"></i>
                        <span id="payBtnText">{{ __('central.XpayPayNow', ['amount' => number_format($amount, 2), 'code' => $chargeCurrency]) }}</span>
                    </button>
                    <p class="text-center text-muted mt-3 checkout-secure-note">
                        <i class="bi bi-shield-check"></i>
                        {{ __('central.XpaySecureNote') }}
                    </p>
                </form>
            </div>
        </div>
    </div>

    @php $payLabel = __('central.XpayPayNow', ['amount' => number_format($amount, 2), 'code' => $chargeCurrency]); @endphp
    <script>
    window.XpayCheckout = {
        publishableKey: @json($element['publishable_key']),
        accountId:      @json($element['account_id']),
        hmacSecret:     @json($element['hmac_secret']),
        clientSecret:   @json($clientSecret),
        encryptionKey:  @json($encryptionKey),
        customerName:   @json($customerName),
        confirmUrl:     @json($confirmUrl),
        successUrl:     @json($successUrl),
        csrf:           @json(csrf_token()),
        trans: {
            pay:        @json($payLabel),
            processing: @json(__('central.XpayProcessing')),
            verifying:  @json(__('central.XpayVerifying')),
            failed:     @json(__('central.XpayPaymentFailed')),
            sdkMissing: @json(__('central.XpaySdkUnavailable')),
        }
    };

    (function () {
        var cfg     = window.XpayCheckout;
        var btn     = document.getElementById('payBtn');
        var btnText = document.getElementById('payBtnText');
        var errBox  = document.getElementById('xpayError');
        var errText = document.getElementById('xpayErrorText');
        var xpay    = null;
        var busy    = false;

        function showError(msg) {
            errText.textContent = msg || cfg.trans.failed;
            errBox.classList.add('show');
        }
        function clearError() { errBox.classList.remove('show'); }
        function setBusy(state, label) {
            busy = state;
            btn.disabled = state;
            btnText.textContent = label || cfg.trans.pay;
        }

        // The SDK script is deferred: mount the element once it is available.
        var tries = 0;
        var timer = setInterval(function () {
            tries++;
            if (typeof window.Xpay === 'function') {
                clearInterval(timer);
                try {
                    xpay = new Xpay(cfg.publishableKey, cfg.accountId, cfg.hmacSecret);
                    xpay.element('#xpay-element', {
                        override: true,
                        fields: {
                            creditCard: { placeholder: '1234 1234 1234 1234' },
                            exp:        { placeholder: 'MM / YY' }
                        }
                    });
                    btn.disabled = false;
                } catch (e) {
                    console.error(e);
                    showError(cfg.trans.sdkMissing);
                }
            } else if (tries > 100) {
                clearInterval(timer);
                showError(cfg.trans.sdkMissing);
            }
        }, 100);

        // Server-side confirmation: the intent is re-fetched from XPay before
        // anything is marked paid. Retries a few times for late settlement.
        function confirmOnServer(attempt) {
            attempt = attempt || 1;
            setBusy(true, cfg.trans.verifying);
            return fetch(cfg.confirmUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': cfg.csrf },
                body: JSON.stringify({})
            })
            .then(function (res) { return res.json().then(function (j) { j._status = res.status; return j; }); })
            .then(function (data) {
                if (data.confirmed) {
                    window.location.href = data.redirect || cfg.successUrl;
                    return;
                }
                if (!data.final && attempt < 6 && (data._status === 200)) {
                    return new Promise(function (r) { setTimeout(r, 2000); }).then(function () { return confirmOnServer(attempt + 1); });
                }
                showError(data.message || cfg.trans.failed);
                setBusy(false);
            })
            .catch(function (e) {
                console.error(e);
                showError(cfg.trans.failed);
                setBusy(false);
            });
        }

        btn.addEventListener('click', function () {
            if (busy || !xpay) return;
            clearError();
            setBusy(true, cfg.trans.processing);

            var customer = { name: cfg.customerName || 'Customer' };

            Promise.resolve()
                .then(function () { return xpay.confirmPayment('card', cfg.clientSecret, customer, cfg.encryptionKey); })
                .then(function (res) {
                    if (res && res.error) {
                        showError(res.message || cfg.trans.failed);
                        setBusy(false);
                        return;
                    }
                    return confirmOnServer(1);
                })
                .catch(function (err) {
                    console.error(err);
                    showError((err && err.message) ? err.message : cfg.trans.failed);
                    setBusy(false);
                });
        });
    })();
    </script>
</body>
</html>
