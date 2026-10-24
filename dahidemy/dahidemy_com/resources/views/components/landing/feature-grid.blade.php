@props(['cores' => collect()])

@php
    $iconCycle = ['user-plus', 'book-open', 'check-circle', 'clipboard', 'wallet', 'chat', 'library', 'bus', 'home', 'shield', 'chart', 'globe'];
@endphp

<section id="features" class="py-20 sm:py-28">
    <div class="container-page">
        <x-landing.section-heading
            eyebrow="Everything you need"
            title='One platform, <span class="gradient-text">every workflow</span>'
            subtitle="From the first admission enquiry to alumni relations, Dahidemy unifies every department so nothing falls through the cracks." />

        <div class="mt-14 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($cores as $i => $core)
                @php $core = (array) $core; @endphp
                <article class="reveal card group hover:-translate-y-1 hover:border-brand-300 hover:shadow-[var(--shadow-glow)] dark:hover:border-brand-500/40">
                    <div class="inline-flex h-12 w-12 items-center justify-center overflow-hidden rounded-xl bg-gradient-to-br from-brand-500 to-accent-500 text-white shadow-lg shadow-brand-600/25 transition group-hover:scale-110">
                        <x-landing.icon :name="$core['icon'] ?? $iconCycle[$i % count($iconCycle)]" storage="saas_cores"
                            class="h-6 w-6" />
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-slate-900 dark:text-white">
                        {{ $core['title'] ?? $core['name'] ?? 'Feature' }}
                    </h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                        {{ $core['description'] ?? $core['subtitle'] ?? '' }}
                    </p>
                </article>
            @empty
                <div class="sm:col-span-2 lg:col-span-3">
                    <x-landing.empty title="No features published yet"
                        message="Core features added from the admin panel will appear here." />
                </div>
            @endforelse
        </div>
    </div>
</section>
