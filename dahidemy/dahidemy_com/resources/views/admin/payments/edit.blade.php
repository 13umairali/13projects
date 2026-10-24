@extends('layouts.admin')

@section('title', 'Edit gateway')

@section('content')
    <x-admin.page-header :title="'Configure ' . str_replace('_', ' ', $gateway['name'] ?? 'gateway')"
        subtitle="Update credentials, mode and availability. Fields map to the gateway's API keys."
        :back="route('admin.payments.index')" />

    <form method="POST" action="{{ route('admin.payments.update', $id) }}" class="max-w-2xl space-y-6">
        @csrf
        @method('PUT')

        <div class="card space-y-5">
            <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-400">Credentials</h3>

            @if (count($fields))
                <div class="grid gap-5 sm:grid-cols-2">
                    @foreach ($fields as $key => $value)
                        <x-admin.field :field="[
                            'name' => 'payment_info[' . $key . ']',
                            'label' => \Illuminate\Support\Str::headline($key),
                            'type' => 'text',
                            'rules' => [],
                        ]" :value="$value" />
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-500 dark:text-slate-400">This gateway has no editable credential fields.</p>
            @endif
        </div>

        <div class="card space-y-5">
            <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-400">Availability</h3>
            <div class="grid gap-5 sm:grid-cols-2">
                <x-admin.field :field="[
                    'name' => 'mode', 'label' => 'Mode', 'type' => 'select',
                    'options' => ['test' => 'Test / Sandbox', 'live' => 'Live'], 'rules' => ['required'],
                ]" :value="$gateway['mode'] ?? 'test'" />
                <x-admin.field :field="[
                    'name' => 'status', 'label' => 'Status', 'type' => 'select',
                    'options' => ['1' => 'Active', '0' => 'Inactive'], 'rules' => ['required'],
                ]" :value="(string) ($gateway['status'] ?? '0')" />
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary px-5 py-2.5">Save gateway</button>
            <a href="{{ route('admin.payments.index') }}" class="btn-ghost px-5 py-2.5">Cancel</a>
        </div>
    </form>
@endsection
