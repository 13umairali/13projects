@php $isEdit = $mode === 'edit'; @endphp

@extends('layouts.admin')

@section('title', ($isEdit ? 'Edit ' : 'New ') . \Illuminate\Support\Str::lower($def['singular']))

@section('content')
    <x-admin.page-header
        :title="($isEdit ? 'Edit ' : 'New ') . \Illuminate\Support\Str::lower($def['singular'])"
        :subtitle="$def['description'] ?? null"
        :back="route('admin.content.index', $resource)" />

    <form method="POST"
        action="{{ $isEdit ? route('admin.content.update', [$resource, $id]) : route('admin.content.store', $resource) }}"
        enctype="multipart/form-data" class="card max-w-3xl space-y-5">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        @foreach ($def['fields'] as $field)
            <x-admin.field :field="$field" :value="$record[$field['name']] ?? null"
                :storage="\App\Support\ResourceRegistry::storageDir($def)" />
        @endforeach

        <div class="flex items-center gap-3 border-t border-slate-100 pt-5 dark:border-white/10">
            <button type="submit" class="btn-primary px-5 py-2.5">
                {{ $isEdit ? 'Save changes' : 'Create ' . \Illuminate\Support\Str::lower($def['singular']) }}
            </button>
            <a href="{{ route('admin.content.index', $resource) }}" class="btn-ghost px-5 py-2.5">Cancel</a>
        </div>
    </form>
@endsection
