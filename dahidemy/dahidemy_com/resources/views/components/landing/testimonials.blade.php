@props(['feedback' => collect()])

<section class="border-y border-slate-200 bg-slate-50/60 py-20 sm:py-28 dark:border-white/10 dark:bg-white/[0.02]">
    <div class="container-page">
        <x-landing.section-heading
            eyebrow="Loved by educators"
            title='Trusted by institutions <span class="gradient-text">worldwide</span>'
            subtitle="Thousands of administrators, teachers and parents rely on Mighty School every day." />

        <div class="mt-14 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @forelse ($feedback as $item)
                @php
                    // Column names are s_a_a_s_feedback's: description, note,
                    // ratting (sic), thumbnail_image. Not comment/designation/rating.
                    $item = (array) $item;
                    $name = $item['name'] ?? '';
                    $rating = (int) ($item['ratting'] ?? 0);
                    $video = $item['video_url'] ?? null;
                @endphp
                <figure class="reveal card flex flex-col">
                    @if ($rating > 0)
                        <div class="flex gap-0.5 text-amber-400">
                            @for ($s = 0; $s < 5; $s++)
                                <svg class="h-4 w-4" fill="{{ $s < $rating ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="1.5" d="M12 2l3 6.5 7 .9-5 4.8 1.3 7L12 18l-6.3 3.2L7 14.2 2 9.4l7-.9z"/></svg>
                            @endfor
                        </div>
                    @endif

                    <blockquote class="mt-4 grow text-sm leading-relaxed text-slate-700 dark:text-slate-300">
                        “{{ $item['description'] ?? '' }}”
                    </blockquote>

                    <figcaption class="mt-6 flex items-center gap-3">
                        <x-img :src="$item['thumbnail_image'] ?? null" dir="saas_feedbacks" kind="avatar"
                            :alt="$name" class="h-10 w-10 flex-none rounded-full ring-1 ring-slate-200 dark:ring-white/10" />
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $name }}</p>
                            <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $item['note'] ?? '' }}</p>
                        </div>
                        @if ($video)
                            <a href="{{ $video }}" target="_blank" rel="noopener" aria-label="Watch video testimonial"
                                class="ml-auto grid h-8 w-8 flex-none place-items-center rounded-full bg-brand-50 text-brand-600 transition hover:bg-brand-100 dark:bg-brand-500/10 dark:text-brand-300">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            </a>
                        @endif
                    </figcaption>
                </figure>
            @empty
                <div class="md:col-span-2 lg:col-span-3">
                    <x-landing.empty title="No testimonials yet"
                        message="Customer testimonials added from the admin panel will appear here."
                        icon="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8z" />
                </div>
            @endforelse
        </div>
    </div>
</section>
