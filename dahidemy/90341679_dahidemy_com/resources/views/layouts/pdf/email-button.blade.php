<button type="button" class="btn-download" onclick="document.getElementById('email-cert-modal-{{ $studentSession->id }}').style.display='flex'"
    style="border: none; background: transparent; cursor: pointer;">
    <img src="{{ asset('icons/download.png') }}" alt="" style="width: 15px; margin-right: 10px;">
    {{ __('Email') }}
</button>

<div id="email-cert-modal-{{ $studentSession->id }}" class="noprint"
    style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,.5); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; padding:24px; border-radius:8px; width:100%; max-width:400px;">
        <h5 style="margin-top:0;">{{ _lang('Email this certificate') }}</h5>
        <form method="POST" action="{{ route('layout-cert.email') }}">
            @csrf
            <input type="hidden" name="type" value="{{ $template->type }}">
            <input type="hidden" name="class_id" value="{{ $studentSession->class_id }}">
            <input type="hidden" name="section_id" value="{{ $studentSession->section_id }}">
            <input type="hidden" name="roll" value="{{ $studentSession->roll }}">

            <div class="form-group" style="margin-bottom:16px;">
                <label>{{ _lang('Send to email address') }}</label>
                <input type="email" name="email" class="form-control" required
                    placeholder="{{ _lang('parent@example.com') }}">
            </div>

            <div style="text-align:right;">
                <button type="button" class="btn btn-secondary btn-sm"
                    onclick="document.getElementById('email-cert-modal-{{ $studentSession->id }}').style.display='none'">
                    {{ _lang('Cancel') }}
                </button>
                <button type="submit" class="btn btn-primary btn-sm">
                    {{ _lang('Send') }}
                </button>
            </div>
        </form>
    </div>
</div>
