@extends('themes.' . config('frontend.active_theme', 'theme-three') . '.layouts.app')

@section('title', 'Forms')

@section('content')
    <section class="py-20 bg-slate-50">
        <div class="max-w-5xl mx-auto px-4">
            <div class="text-center mb-14">
                <span class="eyebrow mb-5">Get Started</span>
                <h1 class="section-title">Available Forms</h1>
                <p class="text-slate-500 mt-4">{{ $settings['school_name'] ?? $institute->name ?? '' }}</p>
            </div>

            @if ($forms->isEmpty())
                <div class="text-center py-20 text-slate-400">
                    <i class="fa-solid fa-file-circle-question text-4xl mb-4"></i>
                    <p>No forms are available right now.</p>
                </div>
            @else
                <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-8">
                    @foreach ($forms as $form)
                        <a href="{{ route('public-form.show', $form->slug) }}" class="card-elevated group p-8">
                            <div class="w-16 h-16 rounded-2xl bg-brand-50 text-brand flex items-center justify-center text-2xl mb-6 group-hover:bg-brand-600 group-hover:text-white transition-colors duration-300">
                                <i class="{{ $form->icon ?: 'fa-solid fa-file-lines' }}"></i>
                            </div>
                            <h3 class="text-xl font-bold mb-2 font-display">{{ $form->name }}</h3>
                            <p class="text-slate-500 text-sm leading-6 mb-6">
                                {{ Str::limit($form->description ?: 'Complete this form to get started.', 100) }}
                            </p>
                            <span class="inline-flex items-center gap-2 text-brand font-semibold text-sm">
                                {{ $form->submit_button_text ?: 'Open Form' }} <i class="fa-solid fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                            </span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection