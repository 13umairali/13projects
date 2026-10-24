{{-- =========================================================
    NEW SECTION — the homepage controller already fetches
    $data['notices'] but the old theme never rendered it.
========================================================= --}}
@if ($data['notices'] && count($data['notices']) > 0)
    <section class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12" data-aos="fade-up">
                <div>
                    <span class="eyebrow mb-4">Notice Board</span>
                    <h2 class="section-title">Latest Announcements</h2>
                </div>
            </div>

            <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-6">
                @foreach ($data['notices'] as $notice)
                    <div class="card-elevated p-7 flex gap-5" data-aos="fade-up" data-aos-delay="{{ $loop->iteration * 80 }}">
                        <div class="shrink-0 w-16 h-16 rounded-2xl bg-brand-50 text-brand flex flex-col items-center justify-center leading-none">
                            <span class="text-xl font-black font-display">{{ \Carbon\Carbon::parse($notice->date ?? $notice->created_at)->format('d') }}</span>
                            <span class="text-[11px] font-semibold uppercase">{{ \Carbon\Carbon::parse($notice->date ?? $notice->created_at)->format('M') }}</span>
                        </div>
                        <div class="min-w-0">
                            <h3 class="font-bold text-lg mb-1.5 truncate font-display">{{ $notice->title }}</h3>
                            <p class="text-sm text-slate-500 leading-6 line-clamp-3">
                                {{ Str::limit(strip_tags($notice->notice ?? ''), 130) }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
