@props(['solutions' => collect()])

<section id="solutions" class="border-y border-slate-200 bg-slate-50/60 py-20 sm:py-28 dark:border-white/10 dark:bg-white/[0.02]">
    <div class="container-page">
        <x-landing.section-heading
            eyebrow="Built for every institution"
            title='Solutions that fit <span class="gradient-text">how you work</span>'
            subtitle="Whether you run a single school or a network of campuses, Dahidemy adapts to your structure." />

        <div class="mt-14 grid gap-6 lg:grid-cols-2">
            @forelse ($solutions as $i => $solution)
                @php $solution = (array) $solution; @endphp
                <article class="reveal group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-8 transition hover:shadow-[var(--shadow-glow)] dark:border-white/10 dark:bg-slate-900">
                    <div class="absolute right-0 top-0 h-24 w-24 -translate-y-8 translate-x-8 rounded-full bg-brand-500/10 blur-2xl transition group-hover:bg-brand-500/20"></div>

                    <div class="flex items-center gap-3">
                        <span
                            class="grid h-11 w-11 flex-none place-items-center overflow-hidden rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                            <x-landing.icon :name="$solution['icon'] ?? 'sparkles'" storage="saas_solutions"
                                class="h-5 w-5" />
                        </span>
                        <span class="text-sm font-bold text-brand-600 dark:text-brand-400">0{{ $i + 1 }}</span>
                    </div>

                    <h3 class="mt-3 text-xl font-semibold text-slate-900 dark:text-white">
                        {{ $solution['title'] ?? $solution['name'] ?? 'Solution' }}
                    </h3>
                    <p class="mt-2 max-w-md text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                        {{ $solution['description'] ?? $solution['subtitle'] ?? '' }}
                    </p>
                    <a href="{{ route('features') }}" class="mt-5 inline-flex items-center gap-1.5 text-sm font-semibold text-brand-600 transition group-hover:gap-2.5 dark:text-brand-400">
                        Learn more
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                    </a>
                </article>
            @empty
                <div class="lg:col-span-2">
                    <x-landing.empty title="No solutions published yet"
                        message="Solutions added from the admin panel will appear here." />
                </div>
            @endforelse
        </div>
    </div>
</section>
