@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Course Content') }} — {{ $course->title }}
@endsection

@section('create-button')
    <a href="{{ route('elearning.admin.courses.edit', $course->id) }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('Back to Course Details') }}
    </a>
@endsection

@section('content')
    <div class="row">
        {{-- ===================== CHAPTERS & LESSONS ===================== --}}
        <div class="col-lg-8">
            <div class="card mb-6">
                <div class="card-header">
                    <h3 class="card-title">{{ _lang('Chapters & Lessons') }}</h3>
                </div>
                <div class="card-body">
                    @forelse ($course->courseChapters as $chapter)
                        <div class="border rounded-3 p-4 mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">{{ $chapter->title }}</h5>
                                <form action="{{ route('elearning.admin.courses.chapters.destroy', [$course->id, $chapter->id]) }}" method="POST"
                                    onsubmit="return confirm('{{ _lang('Delete this chapter and all its lessons?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-icon btn-light-danger"><i class="fa fa-trash"></i></button>
                                </form>
                            </div>

                            {{-- Lessons in this chapter --}}
                            @forelse ($chapter->lessons as $lesson)
                                <div class="d-flex justify-content-between align-items-center px-3 py-2 rounded bg-light-subtle mb-2">
                                    <span><i class="fa fa-play-circle text-muted me-2"></i>{{ $lesson->title }}</span>
                                    <form action="{{ route('elearning.admin.courses.lessons.destroy', [$course->id, $lesson->id]) }}" method="POST"
                                        onsubmit="return confirm('{{ _lang('Delete this lesson?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon btn-light-danger"><i class="fa fa-trash fa-xs"></i></button>
                                    </form>
                                </div>
                            @empty
                                <p class="text-muted fs-8 mb-2">{{ _lang('No lessons in this chapter yet.') }}</p>
                            @endforelse

                            {{-- Add lesson to this chapter --}}
                            <form action="{{ route('elearning.admin.courses.lessons.store', [$course->id, $chapter->id]) }}" method="POST" class="mt-3 p-3 bg-light-subtle rounded">
                                @csrf
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-4">
                                        <input type="text" name="title" class="form-control form-control-sm" placeholder="{{ _lang('Lesson title') }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <select name="video_type" class="form-select form-select-sm">
                                            <option value="none">{{ _lang('No video') }}</option>
                                            <option value="youtube">{{ _lang('YouTube') }}</option>
                                            <option value="vimeo">{{ _lang('Vimeo') }}</option>
                                            <option value="embed">{{ _lang('Embed URL') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" name="video_url" class="form-control form-control-sm" placeholder="{{ _lang('Video URL (optional)') }}">
                                    </div>
                                    <div class="col-md-1">
                                        <button type="submit" class="btn btn-sm btn-primary w-100"><i class="fa fa-plus"></i></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @empty
                        <p class="text-muted text-center py-6">{{ _lang('No chapters yet — add one below to get started.') }}</p>
                    @endforelse

                    {{-- Add chapter --}}
                    <form action="{{ route('elearning.admin.courses.chapters.store', $course->id) }}" method="POST" class="d-flex gap-2 mt-4">
                        @csrf
                        <input type="text" name="title" class="form-control" placeholder="{{ _lang('New chapter title') }}" required>
                        <button type="submit" class="btn btn-primary whitespace-nowrap">
                            <i class="fa fa-plus me-1"></i> {{ _lang('Add Chapter') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- ===================== FEATURES + FAQS ===================== --}}
        <div class="col-lg-4">
            <div class="card mb-6">
                <div class="card-header">
                    <h3 class="card-title">{{ _lang('Features') }}</h3>
                </div>
                <div class="card-body">
                    @forelse ($course->courseFeatures as $feature)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fs-7"><i class="fa fa-check text-success me-2"></i>{{ $feature->name }}</span>
                            <form action="{{ route('elearning.admin.courses.features.destroy', [$course->id, $feature->id]) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-icon btn-light-danger"><i class="fa fa-trash fa-xs"></i></button>
                            </form>
                        </div>
                    @empty
                        <p class="text-muted fs-8">{{ _lang('No features yet.') }}</p>
                    @endforelse

                    <form action="{{ route('elearning.admin.courses.features.store', $course->id) }}" method="POST" class="d-flex gap-2 mt-3">
                        @csrf
                        <input type="text" name="name" class="form-control form-control-sm" placeholder="{{ _lang('e.g. Lifetime access') }}" required>
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-plus"></i></button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ _lang('FAQs') }}</h3>
                </div>
                <div class="card-body">
                    @forelse ($course->courseFaqs as $faq)
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="fs-8">
                                <strong class="d-block">{{ $faq->question }}</strong>
                                <span class="text-muted">{{ Str::limit($faq->answer, 80) }}</span>
                            </div>
                            <form action="{{ route('elearning.admin.courses.faqs.destroy', [$course->id, $faq->id]) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-icon btn-light-danger"><i class="fa fa-trash fa-xs"></i></button>
                            </form>
                        </div>
                    @empty
                        <p class="text-muted fs-8">{{ _lang('No FAQs yet.') }}</p>
                    @endforelse

                    <form action="{{ route('elearning.admin.courses.faqs.store', $course->id) }}" method="POST" class="mt-3">
                        @csrf
                        <input type="text" name="question" class="form-control form-control-sm mb-2" placeholder="{{ _lang('Question') }}" required>
                        <textarea name="answer" class="form-control form-control-sm mb-2" rows="2" placeholder="{{ _lang('Answer') }}" required></textarea>
                        <button type="submit" class="btn btn-sm btn-primary w-100"><i class="fa fa-plus me-1"></i> {{ _lang('Add FAQ') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
