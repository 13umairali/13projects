@extends('layouts.admin')

@section('title', 'Edit institute')

@section('content')
    @php
        $logo = saas_image($institute['logo'] ?? null, \App\Support\ImageUrl::DIR_INSTITUTE);
    @endphp

    <x-admin.page-header title="Edit institute"
        :subtitle="$institute['name'] ?? null"
        :back="route('admin.institutes.index')" />

    <form method="POST" action="{{ route('admin.institutes.update', $id) }}" enctype="multipart/form-data" class="max-w-3xl space-y-6">
        @csrf
        @method('PUT')

        <div class="card space-y-5">
            <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-400">Institute details</h3>
            <div class="grid gap-5 sm:grid-cols-2">
                <x-admin.field :field="['name' => 'name', 'label' => 'Institute name', 'type' => 'text', 'rules' => ['required']]" :value="$institute['name'] ?? ''" />
                <x-admin.field :field="['name' => 'email', 'label' => 'Institute email', 'type' => 'email', 'rules' => []]" :value="$institute['email'] ?? ''" />
                <x-admin.field :field="['name' => 'phone', 'label' => 'Phone', 'type' => 'text', 'rules' => ['required']]" :value="$institute['phone'] ?? ''" />
                <x-admin.field :field="['name' => 'domain', 'label' => 'Domain', 'type' => 'text', 'rules' => ['required']]" :value="$institute['domain'] ?? ''" />
                <div class="sm:col-span-2">
                    <x-admin.field :field="['name' => 'address', 'label' => 'Address', 'type' => 'textarea', 'rules' => []]" :value="$institute['address'] ?? ''" />
                </div>
                <div class="sm:col-span-2">
                    <x-admin.file-field name="logo" label="Logo" kind="logo" help="Upload to replace the current logo · JPG, PNG or WEBP · max 2MB." :current="$logo" />
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary px-5 py-2.5">Save changes</button>
            <a href="{{ route('admin.institutes.index') }}" class="btn-ghost px-5 py-2.5">Cancel</a>
        </div>
    </form>
@endsection
