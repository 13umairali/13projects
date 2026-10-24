@props(['brand' => [], 'banner' => null])

@php
    // s_a_a_s_banners columns: title, description, image, button_name/link,
    // button_two_name/link. (There is no `subtitle` column.)
    $banner = (array) ($banner ?? []);

    $headline = $banner['title'] ?? 'Run your entire institution from one platform';
    $subtitle = $banner['description'] ?? ($brand['description'] ?? config('saas.brand.description'));

    $ctaOne = filled($banner['button_name'] ?? null)
        ? ['label' => $banner['button_name'], 'href' => $banner['button_link'] ?: route('register.show')]
        : ['label' => 'Start free trial', 'href' => route('register.show')];

    $ctaTwo = filled($banner['button_two_name'] ?? null)
        ? ['label' => $banner['button_two_name'], 'href' => $banner['button_two_link'] ?: route('contact')]
        : ['label' => 'Book a demo', 'href' => route('contact')];
@endphp

<section class="relative overflow-hidden pt-28 pb-16 sm:pt-36">
    <div class="hero-aurora absolute inset-0 -z-10"></div>
    <div class="hero-grid absolute inset-x-0 top-0 -z-10 h-[520px]"></div>

    <div class="container-page">
        <div class="mx-auto max-w-3xl text-center">
            <span class="eyebrow reveal">
                <span class="inline-flex h-2 w-2 rounded-full bg-brand-500"></span>
                All-in-one School ERP &amp; Management
            </span>

            <h1 class="reveal mt-6 text-4xl font-bold tracking-tight text-slate-900 sm:text-6xl dark:text-white">
                {{ \Illuminate\Support\Str::before($headline, ' ') }}
                <span class="gradient-text">{{ \Illuminate\Support\Str::after($headline, ' ') }}</span>
            </h1>

            <p class="reveal mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-slate-600 dark:text-slate-300">
                {{ $subtitle }}
            </p>

            <div class="reveal mt-9 flex flex-col items-center justify-center gap-3 sm:flex-row">
                <a href="{{ $ctaOne['href'] }}" class="btn-primary w-full sm:w-auto">
                    {{ $ctaOne['label'] }}
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                </a>
                <a href="{{ $ctaTwo['href'] }}" class="btn-ghost w-full sm:w-auto">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M23 7l-7 5 7 5V7z"/><rect x="1" y="5" width="15" height="14" rx="2"/></svg>
                    {{ $ctaTwo['label'] }}
                </a>
            </div>

            <p class="reveal mt-5 text-sm text-slate-500 dark:text-slate-400">
                No credit card required · Free plan available · Cancel anytime
            </p>
        </div>

        {{-- Hero visual: the banner image when one is published, otherwise the
             abstract product frame below (shapes only — no invented figures). --}}
        <div class="reveal relative mx-auto mt-16 max-w-5xl">
            <div class="absolute -inset-x-8 -top-8 bottom-0 -z-10 rounded-[2rem] bg-gradient-to-b from-brand-500/20 to-transparent blur-2xl"></div>

            @if (filled($banner['image'] ?? null))
                <x-img :src="$banner['image']" dir="saas_banners" kind="image" :alt="$headline"
                    class="w-full rounded-2xl border border-slate-200 shadow-[var(--shadow-glow)] dark:border-white/10" />
            @else
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-[var(--shadow-glow)] dark:border-white/10 dark:bg-slate-900">
                {{-- Window chrome --}}
                <div class="flex items-center gap-2 border-b border-slate-100 px-4 py-3 dark:border-white/10">
                    <span class="h-3 w-3 rounded-full bg-red-400"></span>
                    <span class="h-3 w-3 rounded-full bg-amber-400"></span>
                    <span class="h-3 w-3 rounded-full bg-emerald-400"></span>
                    <span class="ml-3 hidden rounded-md bg-slate-100 px-3 py-1 text-xs text-slate-400 sm:block dark:bg-white/5">
                        app.{{ \Illuminate\Support\Str::slug($brand['name'] ?? 'mightyschool') }}.com/dashboard
                    </span>
                </div>
                {{-- Abstract dashboard illustration (no fabricated figures) --}}
                <div class="grid gap-4 p-5 sm:grid-cols-3">
                    @foreach (['home', 'wallet', 'check-circle'] as $icon)
                        <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 dark:border-white/5 dark:bg-white/5">
                            <span class="grid h-8 w-8 place-items-center rounded-lg bg-brand-500/10 text-brand-600 dark:text-brand-300">
                                <x-landing.icon :name="$icon" class="h-4 w-4" />
                            </span>
                            <div class="mt-3 h-2.5 w-2/3 rounded-full bg-slate-200 dark:bg-white/10"></div>
                            <div class="mt-2 h-2 w-1/3 rounded-full bg-slate-200/70 dark:bg-white/5"></div>
                        </div>
                    @endforeach
                </div>
                <div class="px-5 pb-6">
                    <div class="flex h-40 items-end gap-2 rounded-xl border border-slate-100 bg-slate-50 p-4 dark:border-white/5 dark:bg-white/5">
                        @foreach ([45, 62, 38, 72, 55, 84, 60, 92, 70, 48, 78, 66] as $h)
                            <div class="flex-1 rounded-t bg-gradient-to-t from-brand-500/70 to-accent-500/70" style="height: {{ $h }}%"></div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</section>
