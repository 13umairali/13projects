@extends('layouts.admin')

@section('title', 'Settings')

@section('content')
    <x-admin.page-header title="Settings"
        subtitle="Every SaaS setting the backend holds — site details, social links, theme and deployment.">
        <a href="{{ route('admin.branding.index') }}" class="btn-ghost px-4 py-2.5">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <rect x="3" y="3" width="18" height="18" rx="2" />
                <circle cx="8.5" cy="8.5" r="1.5" />
                <path d="M21 15l-5-5L5 21" />
            </svg>
            Logos & branding
        </a>
        <a href="{{ route('admin.settings.mail') }}" class="btn-ghost px-4 py-2.5">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M4 4h16v16H4z" />
                <path d="M22 6l-10 7L2 6" />
            </svg>
            Mail configuration
        </a>
    </x-admin.page-header>

    @unless ($reachable)
        <div
            class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
            <span class="font-semibold">Live data unavailable.</span>
            The backend API could not be reached, so the fields below are empty — check
            <code class="rounded bg-amber-100 px-1 dark:bg-amber-500/20">SAAS_API_URL</code> and that you are signed in.
            Saving now would overwrite stored settings with blanks.
        </div>
    @endunless

    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6"
        x-data="{ saving: false }" @submit="saving = true">
        @csrf

        @foreach ($groups as $group)
            <section class="card space-y-5">
                <div>
                    <h3 class="font-semibold text-slate-900 dark:text-white">{{ $group['title'] }}</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ $group['subtitle'] }}</p>
                </div>

                @if ($group['colors'] ?? false)
                    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($group['fields'] as $key => $field)
                            <x-admin.color-field :name="$key" :label="$field['label']" :value="$values[$key] ?? ''"
                                :help="$field['help'] ?? null" />
                        @endforeach
                    </div>
                @else
                    <div class="grid gap-5 sm:grid-cols-2">
                        @foreach ($group['fields'] as $key => $field)
                            @php
                                // A stored secret is never echoed back into the page source;
                                // an empty submission leaves it untouched on the backend.
                                $value = ($field['type'] ?? 'text') === 'password' ? '' : $values[$key] ?? '';
                            @endphp
                            <div class="{{ ($field['span'] ?? false) ? 'sm:col-span-2' : '' }}">
                                <x-admin.field :field="[
                                    'name' => $key,
                                    'label' => $field['label'],
                                    'type' => $field['type'] ?? 'text',
                                    'options' => $field['options'] ?? [],
                                    'help' => $field['help'] ?? null,
                                    'rules' => $field['rules'],
                                ]" :value="$value" />
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        @endforeach

        {{-- Payment gateways: read-only here; credentials, mode and status are owned
             by the payments page, which is the only endpoint that can write them. --}}
        <section class="card space-y-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h3 class="font-semibold text-slate-900 dark:text-white">Payment gateways</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Configured on the payments page — credentials are never edited from here.
                    </p>
                </div>
                <a href="{{ route('admin.payments.index') }}" class="btn-ghost px-4 py-2.5">Manage gateways</a>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($gateways as $gateway)
                    <div
                        class="flex items-center justify-between rounded-xl border border-slate-100 px-4 py-3 dark:border-white/5">
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $gateway['label'] }}</span>
                        @if ($gateway['configured'])
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                Configured
                            </span>
                        @else
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500 dark:bg-white/5 dark:text-slate-400">
                                <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                Not set
                            </span>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>

        <div
            class="sticky bottom-4 flex items-center justify-end gap-3 rounded-2xl border border-slate-200 bg-white/90 px-5 py-4 shadow-sm backdrop-blur dark:border-white/10 dark:bg-slate-900/90">
            <p class="mr-auto text-sm text-slate-500 dark:text-slate-400">Changes apply across the platform immediately.</p>
            <button type="submit" class="btn-primary px-5 py-2.5" :disabled="saving"
                :class="saving && 'cursor-not-allowed opacity-60'">
                <svg x-show="saving" x-cloak class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                </svg>
                <span x-text="saving ? 'Saving…' : 'Save settings'">Save settings</span>
            </button>
        </div>
    </form>
@endsection
