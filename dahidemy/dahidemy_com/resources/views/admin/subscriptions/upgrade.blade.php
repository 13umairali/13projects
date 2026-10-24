@extends('layouts.admin')

@section('title', 'Manual upgrade')

@section('content')
    <x-admin.page-header title="Manual subscription upgrade"
        subtitle="Upgrade an institute's plan directly — for offline / bank-transfer payments."
        :back="route('admin.subscriptions.requests')" />

    <form method="POST" action="{{ route('admin.subscriptions.upgrade.store') }}" class="max-w-2xl space-y-6">
        @csrf

        <div class="card space-y-5">
            <div class="grid gap-5 sm:grid-cols-2">
                @if (count($instituteOptions))
                    <x-admin.field :field="['name' => 'institute_id', 'label' => 'Institute', 'type' => 'select', 'options' => $instituteOptions, 'rules' => ['required']]" />
                @else
                    <x-admin.field :field="['name' => 'institute_id', 'label' => 'Institute ID', 'type' => 'number', 'rules' => ['required'], 'help' => 'Could not load institutes — enter the ID directly.']" />
                @endif

                @if (count($planOptions))
                    <x-admin.field :field="['name' => 'plan_id', 'label' => 'Plan', 'type' => 'select', 'options' => $planOptions, 'rules' => ['required']]" />
                @else
                    <x-admin.field :field="['name' => 'plan_id', 'label' => 'Plan ID', 'type' => 'number', 'rules' => ['required'], 'help' => 'Could not load plans — enter the ID directly.']" />
                @endif

                <x-admin.field :field="['name' => 'extra_days', 'label' => 'Extra days', 'type' => 'number', 'min' => '0', 'rules' => [], 'default' => '0', 'help' => 'Added on top of the plan duration.']" />
                <x-admin.field :field="['name' => 'payment_method', 'label' => 'Payment method', 'type' => 'text', 'rules' => ['required'], 'default' => 'Bank Transfer']" />
                <x-admin.field :field="['name' => 'amount_paid', 'label' => 'Amount paid', 'type' => 'number', 'step' => '0.01', 'min' => '0', 'rules' => ['required'], 'default' => '0']" />
                <div class="sm:col-span-2">
                    <x-admin.field :field="['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea', 'rules' => []]" />
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary px-5 py-2.5">Apply upgrade</button>
            <a href="{{ route('admin.subscriptions.requests') }}" class="btn-ghost px-5 py-2.5">Cancel</a>
        </div>
    </form>
@endsection
