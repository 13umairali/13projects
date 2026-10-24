@extends('themes.' . config('frontend.active_theme', 'theme-three') . '.layouts.app')

@section('title', 'Gallery')

@section('content')
    <section class="py-24" style="background: var(--paper-50);">
        <div class="max-w-7xl mx-auto px-4">
            <div class="max-w-2xl mb-16">
                <span class="eyebrow mb-4">Campus Life</span>
                <h1 class="section-title">Gallery</h1>
            </div>

            @if ($academicImages->isEmpty())
                <div class="text-center py-20" style="color: var(--slate-500);">
                    <i class="fa-regular fa-images text-3xl mb-4"></i>
                    <p>No images have been added yet.</p>
                </div>
            @else
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($academicImages as $image)
                        <div class="gallery-tile">
                            <img src="{{ asset('storage/academic_images/' . $image->image) }}" class="w-full h-64 object-cover" alt="{{ $image->title }}">
                            @if ($image->title)
                                <div class="overlay flex items-end p-5">
                                    <p class="text-white font-semibold">{{ $image->title }}</p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
