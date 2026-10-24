@props(['benefits' => collect()])

@php
    // s_a_a_s_benefits is a one-row-per-section table: heading + title +
    // description + up to four bullet lines + an image. Render the first row as
    // the section, and any further rows as additional bullet groups.
    $primary = (array) ($benefits->first() ?? []);

    $bullets = collect([
        $primary['para_title_one'] ?? null,
        $primary['para_title_two'] ?? null,
        $primary['para_title_three'] ?? null,
        $primary['para_title_four'] ?? null,
    ])->filter(fn ($b) => filled($b))->values();
@endphp

<section class="py-20 sm:py-28">
    @if ($primary)
        <div class="container-page grid items-center gap-14 lg:grid-cols-2">
            <div class="reveal">
                @if (filled($primary['heading'] ?? null))
                    <span class="eyebrow">{{ $primary['heading'] }}</span>
                @endif

                <h2 class="mt-4 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl dark:text-white">
                    {{ $primary['title'] ?? '' }}
                </h2>

                @if (filled($primary['description'] ?? null))
                    <p class="mt-4 text-lg leading-relaxed text-slate-600 dark:text-slate-400">
                        {{ $primary['description'] }}
                    </p>
                @endif

                @if ($bullets->isNotEmpty())
                    <ul class="mt-8 space-y-4">
                        @foreach ($bullets as $bullet)
                            <li class="flex gap-4">
                                <span
                                    class="mt-0.5 grid h-8 w-8 flex-none place-items-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.2"
                                        viewBox="0 0 24 24">
                                        <path d="M20 6L9 17l-5-5" />
                                    </svg>
                                </span>
                                <span class="font-medium text-slate-900 dark:text-white">{{ $bullet }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- Visual --}}
            <div class="reveal relative">
                <div
                    class="absolute -inset-4 -z-10 rounded-3xl bg-gradient-to-br from-brand-500/15 to-accent-500/15 blur-2xl">
                </div>
                <x-img :src="$primary['image'] ?? null" dir="saas_benefits" kind="image"
                    :alt="$primary['title'] ?? ''"
                    class="aspect-[4/3] w-full rounded-2xl border border-slate-200 shadow-[var(--shadow-card)] dark:border-white/10" />
            </div>
        </div>
    @else
        <div class="container-page">
            <x-landing.empty title="No benefits published yet"
                message="Add a benefit from the admin panel to feature it here." />
        </div>
    @endif
</section>
