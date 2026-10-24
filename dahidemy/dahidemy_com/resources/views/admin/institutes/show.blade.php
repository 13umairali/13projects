@extends('layouts.admin')

@section('title', $institute['name'] ?? 'Institute')

@section('content')
    @php
        $logo = saas_image($institute['logo'] ?? null, \App\Support\ImageUrl::DIR_INSTITUTE);
        $name = $institute['name'] ?? '—';

        $details = [
            'Email'   => $institute['email'] ?? null,
            'Phone'   => $institute['phone'] ?? null,
            'Domain'  => $institute['domain'] ?? null,
            'Type'    => $institute['institute_type'] ?? null,
            'Address' => $institute['address'] ?? null,
        ];
        $account = [
            'Owner'       => $institute['owner_name'] ?? null,
            'Assigned to' => $institute['assigned_name'] ?? null,
            'Plan'        => $institute['plan_name'] ?? null,
            'Starts'      => !empty($institute['start_date']) ? \Illuminate\Support\Carbon::parse($institute['start_date'])->format('d M Y') : null,
            'Ends'        => !empty($institute['end_date']) ? \Illuminate\Support\Carbon::parse($institute['end_date'])->format('d M Y') : null,
            'Created'     => !empty($institute['created_at']) ? \Illuminate\Support\Carbon::parse($institute['created_at'])->format('d M Y') : null,
        ];
    @endphp

    <x-admin.page-header :title="$name" subtitle="Institute overview" :back="route('admin.institutes.index')">
        <form method="POST" action="{{ route('admin.institutes.toggle-status', $institute['id']) }}">
            @csrf
            <button type="submit" class="btn-ghost px-4 py-2.5">
                {{ \App\Support\Status::isActive($institute['status'] ?? 0) ? 'Deactivate' : 'Activate' }}
            </button>
        </form>
        <a href="{{ route('admin.institutes.edit', $institute['id']) }}" class="btn-primary px-4 py-2.5">Edit</a>
    </x-admin.page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Identity --}}
        <div class="card flex flex-col items-center text-center lg:col-span-1">
            <span class="grid h-20 w-20 place-items-center overflow-hidden rounded-2xl bg-brand-50 text-2xl font-bold text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                <x-img :src="$institute['logo'] ?? null" :dir="\App\Support\ImageUrl::DIR_INSTITUTE" kind="logo"
                    :alt="$name" class="h-full w-full" />
            </span>
            <h3 class="mt-4 text-lg font-bold text-slate-900 dark:text-white">{{ $name }}</h3>
            <p class="text-sm text-slate-400">{{ $institute['domain'] ?? '' }}</p>
            <div class="mt-3">
                <x-admin.status-pill :active="($institute['status'] ?? 0)" />
            </div>
        </div>

        {{-- Details + account --}}
        <div class="space-y-6 lg:col-span-2">
            <div class="card">
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-slate-400">Details</h3>
                <dl class="grid gap-x-6 gap-y-4 sm:grid-cols-2">
                    @foreach ($details as $label => $value)
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-slate-400">{{ $label }}</dt>
                            <dd class="mt-0.5 text-sm text-slate-700 dark:text-slate-200">{{ filled($value) ? $value : '—' }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>

            <div class="card">
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-slate-400">Subscription &amp; account</h3>
                <dl class="grid gap-x-6 gap-y-4 sm:grid-cols-2">
                    @foreach ($account as $label => $value)
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-slate-400">{{ $label }}</dt>
                            <dd class="mt-0.5 text-sm text-slate-700 dark:text-slate-200">{{ filled($value) ? $value : '—' }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        </div>
    </div>
@endsection
