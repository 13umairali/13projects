@props(['packages' => collect(), 'compact' => false])

@php
    // Highlight the middle plan (or the one flagged custom) as "most popular".
    $count = $packages->count();
    $popularIndex = $count > 2 ? (int) floor($count / 2) : ($count - 1);
@endphp

<section id="pricing" class="py-20 sm:py-28">
    <div class="container-page">
        @unless ($compact)
            <x-landing.section-heading
                eyebrow="Simple, transparent pricing"
                title='Plans that grow <span class="gradient-text">with you</span>'
                subtitle="Start free, upgrade when you need more. No hidden fees, cancel anytime." />
        @endunless

        <div class="mx-auto mt-14 grid max-w-6xl gap-6 md:grid-cols-3">
            @forelse ($packages as $i => $plan)
                @php
                    $plan = (array) $plan;
                    $popular = $i === $popularIndex;
                    $price = $plan['is_free'] ?? false ? 0 : (float) ($plan['price'] ?? 0);
                @endphp
                <article @class([
                    'reveal relative flex flex-col rounded-2xl border p-8 transition',
                    'border-brand-500 bg-white shadow-[var(--shadow-glow)] ring-1 ring-brand-500/40 md:-translate-y-3 dark:bg-slate-900' => $popular,
                    'border-slate-200 bg-white hover:border-brand-300 dark:border-white/10 dark:bg-slate-900' => !$popular,
                ])>
                    @if ($popular)
                        <span class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-brand-600 px-3 py-1 text-xs font-semibold text-white shadow">
                            Most popular
                        </span>
                    @endif

                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ $plan['name'] ?? 'Plan' }}</h3>
                    <p class="mt-1 min-h-10 text-sm text-slate-500 dark:text-slate-400">{{ $plan['description'] ?? '' }}</p>

                    <div class="mt-5 flex items-baseline gap-1">
                        @if ($plan['is_custom'] ?? false)
                            <span class="text-4xl font-bold tracking-tight text-slate-900 dark:text-white">Custom</span>
                        @else
                            <span class="text-4xl font-bold tracking-tight text-slate-900 dark:text-white">${{ rtrim(rtrim(number_format($price, 2), '0'), '.') }}</span>
                            <span class="text-sm text-slate-500 dark:text-slate-400">/ {{ $plan['cycle'] ?? 'month' }}</span>
                        @endif
                    </div>

                    <ul class="mt-6 space-y-3 text-sm">
                        @foreach ($plan['features'] ?? [] as $feature)
                            <li class="flex items-start gap-2.5 text-slate-600 dark:text-slate-300">
                                <svg class="mt-0.5 h-4 w-4 flex-none text-brand-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                                {{ $feature }}
                            </li>
                        @endforeach
                    </ul>

                    <a href="{{ route('register.show', ['plan' => $plan['id'] ?? null]) }}"
                        @class([
                            'mt-8 w-full',
                            'btn-primary' => $popular,
                            'btn-ghost' => !$popular,
                        ])>
                        {{ ($plan['is_free'] ?? false) ? 'Start for free' : (($plan['is_custom'] ?? false) ? 'Contact sales' : 'Choose '.($plan['name'] ?? 'plan')) }}
                    </a>
                </article>
            @empty
                <div class="md:col-span-3">
                    <x-landing.empty title="No plans available yet"
                        message="Subscription plans added from the admin panel will appear here."
                        icon="M21 16V8a2 2 0 00-1-1.7l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.7l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z" />
                </div>
            @endforelse
        </div>

        @if ($packages->isNotEmpty())
            <p class="reveal mt-8 text-center text-sm text-slate-500 dark:text-slate-400">
                Need something bespoke? <a href="{{ route('contact') }}" class="font-semibold text-brand-600 hover:underline dark:text-brand-400">Talk to our team</a>.
            </p>
        @endif
    </div>
</section>
