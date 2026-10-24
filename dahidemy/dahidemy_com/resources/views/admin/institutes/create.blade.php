@extends('layouts.admin')

@section('title', 'New institute')

@section('content')
    <x-admin.page-header title="New institute"
        subtitle="Create a tenant account and its owner login."
        :back="route('admin.institutes.index')" />

    <form method="POST" action="{{ route('admin.institutes.store') }}" enctype="multipart/form-data" class="max-w-3xl space-y-6">
        @csrf

        {{-- Institute details --}}
        <div class="card space-y-5">
            <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-400">Institute details</h3>
            <div class="grid gap-5 sm:grid-cols-2">
                <x-admin.field :field="['name' => 'institute_name', 'label' => 'Institute name', 'type' => 'text', 'rules' => ['required']]" />
                <x-admin.field :field="['name' => 'institute_email', 'label' => 'Institute email', 'type' => 'email', 'rules' => ['required']]" />
                <x-admin.field :field="['name' => 'institute_phone', 'label' => 'Phone', 'type' => 'text', 'rules' => ['required']]" />
                @if (count($typeOptions))
                    <x-admin.field :field="['name' => 'institute_type', 'label' => 'Institute type', 'type' => 'select', 'options' => $typeOptions, 'rules' => ['required']]" />
                @else
                    <x-admin.field :field="['name' => 'institute_type', 'label' => 'Institute type', 'type' => 'text', 'rules' => ['required']]" />
                @endif
                <x-admin.field :field="['name' => 'institute_domain', 'label' => 'Domain', 'type' => 'text', 'rules' => ['required'], 'help' => 'Subdomain or custom domain used to reach the tenant.']" />
                <div class="sm:col-span-2">
                    <x-admin.field :field="['name' => 'institute_address', 'label' => 'Address', 'type' => 'textarea', 'rules' => []]" />
                </div>
                <div class="sm:col-span-2">
                    <x-admin.file-field name="institute_logo" label="Logo" kind="logo" help="JPG, PNG or WEBP · max 2MB." />
                </div>
            </div>
        </div>

        {{-- Default school setup --}}
        <div class="card space-y-5">
            <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-400">Default school setup</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Copy settings, mark grades, policies, About Us, and website content from an existing school.
                The new school keeps its own institute and branch IDs.
            </p>
            @if (count($templateOptions))
                <x-admin.field :field="[
                    'name' => 'template_institute_id',
                    'label' => 'Copy setup from',
                    'type' => 'select',
                    'options' => $templateOptions,
                    'rules' => [],
                    'help' => 'Leave empty to use the SaaS default template, or built-in defaults if none is set.',
                ]" :value="$defaultTemplateId" />
            @else
                <p class="text-sm text-slate-500">No existing schools are available to copy from. Built-in defaults will be used.</p>
            @endif
        </div>

        {{-- Owner account --}}
        <div class="card space-y-5">
            <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-400">Owner account</h3>
            <div class="grid gap-5 sm:grid-cols-2">
                <x-admin.field :field="['name' => 'user_name', 'label' => 'Owner name', 'type' => 'text', 'rules' => ['required']]" />
                <x-admin.field :field="['name' => 'user_email', 'label' => 'Owner email', 'type' => 'email', 'rules' => ['required']]" />
                <x-admin.field :field="['name' => 'user_phone', 'label' => 'Owner phone', 'type' => 'text', 'rules' => ['required']]" />
                <div>
                    <x-admin.file-field name="user_avatar" label="Owner avatar" kind="avatar" help="Optional · JPG, PNG or WEBP." />
                </div>
                <x-admin.field :field="['name' => 'password', 'label' => 'Password', 'type' => 'password', 'rules' => ['required'], 'help' => 'Minimum 6 characters.']" />
                <x-admin.field :field="['name' => 'password_confirmation', 'label' => 'Confirm password', 'type' => 'password', 'rules' => ['required']]" />
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary px-5 py-2.5">Create institute</button>
            <a href="{{ route('admin.institutes.index') }}" class="btn-ghost px-5 py-2.5">Cancel</a>
        </div>
    </form>
@endsection
