@php($imp = session('impersonation'))
<div id="impersonation-banner"
    style="position:fixed;left:50%;bottom:18px;transform:translateX(-50%);z-index:99999;display:flex;gap:14px;align-items:center;background:#1e2a3a;color:#fff;padding:10px 10px 10px 18px;border-radius:999px;box-shadow:0 6px 24px rgba(0,0,0,.3);font:14px/1.3 system-ui,-apple-system,Segoe UI,sans-serif;max-width:92vw">
    <span>Viewing as <strong>{{ auth()->user()->name ?? 'admin' }}</strong>@if (! empty($imp['institute_name'])) &middot; {{ $imp['institute_name'] }}@endif</span>
    <form method="POST" action="{{ route('impersonation.leave') }}" style="margin:0">
        @csrf
        <button type="submit"
            style="border:0;cursor:pointer;background:#3b6cf6;color:#fff;padding:8px 16px;border-radius:999px;font:600 14px system-ui,sans-serif">
            {{ ! empty($imp['return_url']) ? 'Back to SaaS Admin' : 'Exit' }}
        </button>
    </form>
</div>
