{{-- =========================================================
    NEW SECTION — Upcoming Exams, sourced from the online exam
    schedule (Modules\Examination). Purely a teaser: no marks,
    no student-specific data, just "what's coming up".
========================================================= --}}
@if (isset($data['upcomingExams']) && $data['upcomingExams']->count() > 0)
    <section class="py-24 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-14" data-aos="fade-up">
                <span class="eyebrow mb-4">Academic Calendar</span>
                <h2 class="section-title">Upcoming Exams</h2>
            </div>

            <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-6">
                @foreach ($data['upcomingExams'] as $exam)
                    <div class="card-elevated p-7" data-aos="fade-up" data-aos-delay="{{ $loop->iteration * 80 }}">
                        <div class="w-14 h-14 rounded-2xl bg-brand-50 text-brand flex flex-col items-center justify-center leading-none mb-5">
                            @if ($exam->start_at)
                                <span class="text-lg font-black font-display">{{ \Carbon\Carbon::parse($exam->start_at)->format('d') }}</span>
                                <span class="text-[10px] font-semibold uppercase">{{ \Carbon\Carbon::parse($exam->start_at)->format('M') }}</span>
                            @else
                                <i class="fa-solid fa-file-pen"></i>
                            @endif
                        </div>
                        <h3 class="font-bold mb-2 font-display line-clamp-2">{{ $exam->name }}</h3>
                        <p class="text-sm text-slate-500 mb-1">
                            {{ $exam->class?->class_name }} {{ $exam->section?->section_name }}
                        </p>
                        @if ($exam->subject)
                            <p class="text-xs text-slate-400">{{ $exam->subject->subject_name }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
