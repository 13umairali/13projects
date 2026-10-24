@php
    $statItems = [
        ['icon' => 'fa-user-graduate', 'value' => $data['teachers']->count(), 'label' => 'Expert Teachers', 'desc' => 'Highly qualified teachers with real classroom experience.'],
        ['icon' => 'fa-book-open-reader', 'value' => $data['courses']->count(), 'label' => 'Premium Courses', 'desc' => 'Skill-focused modern academic and online learning programs.'],
        ['icon' => 'fa-calendar-days', 'value' => $data['events']->count(), 'label' => 'School Events', 'desc' => 'Educational, cultural and technology-driven campus events.'],
        ['icon' => 'fa-star', 'value' => $data['testimonials']->count(), 'label' => 'Happy Reviews', 'desc' => 'Parents and students love our quality education platform.'],
    ];
@endphp

<section class="relative py-24" style="background: var(--paper-50);">
    <div class="max-w-7xl mx-auto px-4 relative z-10">
        <div class="max-w-2xl mb-16" data-aos="fade-up">
            <span class="eyebrow mb-5">School Achievements</span>
            <h2 class="section-title">
                Trusted by thousands of families
            </h2>
        </div>

        <div class="grid sm:grid-cols-2 xl:grid-cols-4 divide-x" style="border-color: var(--line-200);">
            @foreach ($statItems as $stat)
                <div class="px-6 first:pl-0">
                    <i class="fa-solid {{ $stat['icon'] }} text-lg mb-5" style="color: var(--brass-600);"></i>
                    <h3 class="text-5xl font-display mb-2" style="color: var(--navy-800);">
                        <span class="counter-value" data-counter="{{ $stat['value'] }}">0</span>+
                    </h3>
                    <h4 class="text-lg font-semibold mb-3" style="color: var(--ink-900);">{{ $stat['label'] }}</h4>
                    <p class="text-sm leading-6" style="color: var(--slate-500);">{{ $stat['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
