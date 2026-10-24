@php
    $__impersonation = session(\App\Http\Controllers\ImpersonationController::SESSION_KEY);
@endphp

@if(!empty($__impersonation['log_id']) && auth('web')->check())
<div class="impersonation-banner" id="impersonationBanner" role="alert">
    <div class="impersonation-banner-inner">
        <span class="impersonation-banner-icon"><i class="bi bi-incognito"></i></span>
        <div class="impersonation-banner-body">
            <strong class="impersonation-banner-title">{{ __('central.ImpersonationBannerTitle') }}</strong>
            <span class="impersonation-banner-text">
                {{ __('central.ImpersonationBannerText', [
                    'user'  => trim((auth('web')->user()->firstname ?? '') . ' ' . (auth('web')->user()->lastname ?? '')),
                    'admin' => $__impersonation['admin_name'] ?? '',
                ]) }}
            </span>
        </div>
        <form method="POST" action="{{ route('impersonation.leave') }}">
            @csrf
            <button type="submit" class="impersonation-banner-exit">
                {{ __('central.ExitImpersonation') }}
            </button>
        </form>
        <button type="button" class="impersonation-banner-hide" id="impersonationBannerHide"
                title="{{ __('central.Dismiss') }}" aria-label="{{ __('central.Dismiss') }}"
                aria-controls="impersonationBanner" aria-expanded="true">
            <i class="bi bi-x-lg" aria-hidden="true"></i>
        </button>
    </div>
</div>

{{-- Collapsed state: the notice folds into a badge rather than disappearing, so
     the audit warning and the exit route stay one click away. --}}
<button type="button" class="impersonation-banner-restore" id="impersonationBannerRestore" hidden
        title="{{ __('central.ImpersonationBannerTitle') }}" aria-label="{{ __('central.ImpersonationBannerTitle') }}"
        aria-controls="impersonationBanner" aria-expanded="false">
    <i class="bi bi-incognito" aria-hidden="true"></i>
</button>

<style>
    .impersonation-banner{position:fixed;left:50%;bottom:18px;transform:translateX(-50%);z-index:100000;max-width:720px;width:calc(100% - 32px);border-radius:12px;box-shadow:0 8px 28px rgba(0,0,0,.28);background:#7c2d12;border:1px solid #ea580c;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;animation:impersonationUp .35s ease}
    .impersonation-banner-inner{display:flex;align-items:center;gap:14px;padding:12px 18px}
    .impersonation-banner-icon{font-size:22px;line-height:1;flex:0 0 auto;color:#fdba74}
    .impersonation-banner-body{display:flex;flex-direction:column;gap:2px;flex:1 1 auto;min-width:0}
    .impersonation-banner-title{font-size:14px;font-weight:700;color:#fff7ed}
    .impersonation-banner-text{font-size:13px;color:#fed7aa;line-height:1.45}
    .impersonation-banner-exit{flex:0 0 auto;display:inline-block;padding:9px 18px;border-radius:8px;font-size:13px;font-weight:600;border:0;cursor:pointer;color:#7c2d12;background:#fdba74;white-space:nowrap}
    .impersonation-banner-exit:hover{filter:brightness(.94)}
    .impersonation-banner-hide{flex:0 0 auto;display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;padding:0;margin-left:-4px;border:0;border-radius:6px;cursor:pointer;font-size:12px;line-height:1;color:#fed7aa;background:transparent}
    .impersonation-banner-hide:hover{background:rgba(255,255,255,.14);color:#fff7ed}
    .impersonation-banner-restore{position:fixed;left:50%;bottom:18px;transform:translateX(-50%);z-index:100000;display:inline-flex;align-items:center;justify-content:center;width:42px;height:42px;padding:0;border-radius:50%;border:1px solid #ea580c;cursor:pointer;font-size:19px;line-height:1;color:#fdba74;background:#7c2d12;box-shadow:0 8px 28px rgba(0,0,0,.28)}
    .impersonation-banner-restore:hover{filter:brightness(1.12)}
    .impersonation-banner-restore[hidden]{display:none}
    @keyframes impersonationUp{from{opacity:0;transform:translate(-50%,16px)}to{opacity:1;transform:translate(-50%,0)}}
    @media print{.impersonation-banner,.impersonation-banner-restore{display:none}}
</style>

<script>
(function () {
    var KEY = 'impersonation_banner_hidden';
    var banner  = document.getElementById('impersonationBanner');
    var hideBtn = document.getElementById('impersonationBannerHide');
    var restore = document.getElementById('impersonationBannerRestore');
    if (!banner || !hideBtn || !restore) return;

    function read() {
        // Private windows and blocked site data can throw on access.
        try { return window.sessionStorage.getItem(KEY) === '1'; } catch (e) { return false; }
    }
    function write(v) {
        try { window.sessionStorage.setItem(KEY, v ? '1' : '0'); } catch (e) { /* ignore */ }
    }
    function apply(hidden) {
        banner.hidden = hidden;
        restore.hidden = !hidden;
        hideBtn.setAttribute('aria-expanded', hidden ? 'false' : 'true');
        restore.setAttribute('aria-expanded', hidden ? 'false' : 'true');
    }

    // Survives navigation within the impersonated session; a new session starts expanded.
    apply(read());
    hideBtn.addEventListener('click', function () { write(true); apply(true); });
    restore.addEventListener('click', function () { write(false); apply(false); });
}());
</script>
@endif
