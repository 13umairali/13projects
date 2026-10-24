{{-- =========================================================
    NEW SECTION — Recently Published Results, sourced from the
    new Result Publication approval workflow. Deliberately an
    ANNOUNCEMENT only — no marks, no student names, no scores.
    Actual results stay behind login on each student's/parent's
    own "My Results" page.
========================================================= --}}
@if (isset($data['resultAnnouncements']) && $data['resultAnnouncements']->count() > 0)
    <section class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-14" data-aos="fade-up">
                <div>
                    <span class="eyebrow mb-4">Results</span>
                    <h2 class="section-title">Recently Published Results</h2>
                </div>
                <a href="{{ url('/login') }}" class="inline-flex items-center gap-2 font-semibold text-brand shrink-0">
                    Login to View Your Result <i class="fa-solid fa-arrow-right-long"></i>
                </a>
            </div>

            <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-6">
                @foreach ($data['resultAnnouncements'] as $result)
                    <div class="card-elevated p-7" data-aos="fade-up" data-aos-delay="{{ $loop->iteration * 80 }}">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg mb-5">
                            <i class="fa-solid fa-clipboard-check"></i>
                        </div>
                        <h3 class="font-bold mb-2 font-display line-clamp-2">{{ $result->title }}</h3>
                        <p class="text-sm text-slate-500 mb-1">
                            {{ $result->class?->class_name }} {{ $result->section?->section_name }}
                        </p>
                        <p class="text-xs text-slate-400 mb-4">
                            {{ $result->exam?->name }} &middot; {{ $result->subject?->subject_name ?? 'All Subjects' }}
                        </p>
                        <p class="text-xs text-slate-400">
                            <i class="fa-regular fa-calendar"></i> {{ optional($result->published_at)->format('d M Y') }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
