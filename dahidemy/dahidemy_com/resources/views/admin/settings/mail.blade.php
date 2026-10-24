@extends('layouts.admin')

@section('title', 'Mail configuration')

@section('content')
    <x-admin.page-header title="Mail configuration"
        subtitle="Outgoing SMTP settings used for onboarding, billing and notification emails."
        :back="route('admin.settings.index')" />

    @unless ($reachable)
        <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
            <span class="font-semibold">Live data unavailable.</span>
            The backend API could not be reached — check <code class="rounded bg-amber-100 px-1 dark:bg-amber-500/20">SAAS_API_URL</code> and that you are signed in.
        </div>
    @endunless

    <form method="POST" action="{{ route('admin.settings.mail.update') }}" class="max-w-2xl">
        @csrf
        <div class="card space-y-5">
            <div class="grid gap-5 sm:grid-cols-2">
                @foreach ($fields as $key => $label)
                    @php $type = $key === 'MAIL_PASSWORD' ? 'password' : 'text'; @endphp
                    <x-admin.field :field="['name' => $key, 'label' => $label, 'type' => $type, 'rules' => []]"
                        :value="$values[$key] ?? ''" />
                @endforeach
            </div>
            <div>
                <button type="submit" class="btn-primary px-5 py-2.5">Save mail settings</button>
            </div>
        </div>
    </form>
@endsection
