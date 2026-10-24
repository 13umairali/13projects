@extends('setup.main')
@section('content')

    <p class="eyebrow">Step 2 of 4</p>
    <h1>Application settings</h1>
    <p class="lead-text">Name your installation, set its public URL and choose how it runs. You can change all of this later in the .env file.</p>

    <form action="{{ route('setupStep1') }}" method="post">
        @csrf

        <div class="form-grid">
            <div class="form-group full">
                <label for="app_name">Application name</label>
                <span class="tip" title="Shown in the browser tab, emails and the super admin panel"><i class="fa fa-question-circle" aria-hidden="true"></i></span>
                <input type="text" class="form-control" id="app_name" name="app_name" placeholder="Stocky SaaS" value="{{ $data['APP_NAME'] }}" autofocus>
            </div>

            <div class="form-group full">
                <label for="app_url">Application URL</label>
                <span class="tip" title="The full public URL of the central application, e.g. https://yourdomain.com. Tenant workspaces are served as subdomains of this domain."><i class="fa fa-question-circle" aria-hidden="true"></i></span>
                <input type="url" class="form-control" id="app_url" name="app_url" placeholder="https://yourdomain.com" value="{{ old('app_url', session('env.APP_URL') ?: request()->getSchemeAndHttpHost()) }}" required>
                <small class="help-text"><i class="fa fa-info-circle"></i> Use your domain name, not the server IP address — tenant subdomains depend on it.</small>
            </div>

            <div class="form-group">
                <label for="app_env">Environment</label>
                <span class="tip" title="Use 'Production' on a live server. 'Local' is for development only."><i class="fa fa-question-circle" aria-hidden="true"></i></span>
                <select class="form-control" id="app_env" name="app_env">
                    <option value="local" {{ $data['APP_ENV'] == 'local' ? 'selected' : '' }}>Local</option>
                    <option value="testing" {{ $data['APP_ENV'] == 'testing' ? 'selected' : '' }}>Testing</option>
                    <option value="production" {{ !in_array($data['APP_ENV'], ['local', 'testing']) ? 'selected' : '' }}>Production</option>
                </select>
            </div>

            <div class="form-group">
                <label for="app_debug">Debug mode</label>
                <span class="tip" title="Shows detailed error pages. Keep it off in production."><i class="fa fa-question-circle" aria-hidden="true"></i></span>
                <select class="form-control" id="app_debug" name="app_debug">
                    <option value="true" {{ $data['APP_DEBUG'] == 'true' ? 'selected' : '' }}>On</option>
                    <option value="false" {{ $data['APP_DEBUG'] != 'true' ? 'selected' : '' }}>Off</option>
                </select>
            </div>

            <div class="form-group full">
                <label for="app_key">Application key</label>
                <span class="tip" title="A unique key used to encrypt your data. Generate a fresh one for a new installation."><i class="fa fa-question-circle" aria-hidden="true"></i></span>
                <div class="input-row">
                    <input type="text" class="form-control" id="app_key" name="app_key" value="{{ $data['APP_KEY'] }}" placeholder="Click Generate to create a key" readonly>
                    <button type="button" class="btn btn-ghost" id="generate_key" title="Generate a new key">Generate</button>
                </div>
            </div>
        </div>

        <div class="pane-nav">
            <a href="/setup" class="btn btn-ghost"><i class="fa fa-angle-left"></i> Back</a>
            <button type="submit" id="next" class="btn btn-accent">Continue <i class="fa fa-angle-right"></i></button>
        </div>
    </form>

@endsection
