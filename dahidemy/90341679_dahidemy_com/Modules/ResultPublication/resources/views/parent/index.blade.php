@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang("My Children's Results") }}
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            @forelse ($byChild as $entry)
                <div class="card mb-6">
                    <div class="card-header">
                        <h3 class="card-title">{{ $entry['student']->first_name }} {{ $entry['student']->last_name }}</h3>
                    </div>
                    <div class="card-body">
                        @if ($entry['publications']->isEmpty())
                            <p class="text-muted mb-0">{{ _lang('No results have been published yet.') }}</p>
                        @else
                            <div class="row g-5">
                                @foreach ($entry['publications'] as $publication)
                                    <div class="col-md-6">
                                        <div class="border rounded p-4 h-100">
                                            <h5 class="mb-2">{{ $publication->title }}</h5>
                                            <p class="text-muted mb-3 fs-8">
                                                {{ $publication->exam?->name }}
                                                &middot;
                                                {{ $publication->subject?->subject_name ?? _lang('All Subjects') }}
                                            </p>

                                            @if ($publication->description)
                                                <p class="mb-3">{{ $publication->description }}</p>
                                            @endif

                                            @if ($publication->type === 'physical' && $publication->file)
                                                <a href="{{ asset('storage/result_publications/' . $publication->file) }}" target="_blank" class="btn btn-primary btn-sm">
                                                    <i class="fa fa-download me-1"></i> {{ _lang('View / Download') }}
                                                </a>
                                            @elseif ($publication->type === 'online')
                                                <span class="badge badge-light-info">{{ _lang('Online Exam Result') }}</span>
                                            @endif

                                            <p class="text-muted fs-9 mt-3 mb-0">{{ _lang('Published') }} {{ optional($publication->published_at)->format('d M Y') }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-15 text-muted">
                    {{ _lang('No children linked to your account yet.') }}
                </div>
            @endforelse
        </div>
    </div>
@endsection
