@props(['stats' => null, 'revenue' => null])

@php
    // Only real, backend-sourced figures — never fabricated placeholders.
    $stats = collect($stats ?? []);
    $revenue = $revenue ?: null;

    $currency = $revenue['currency'] ?? '';
    // Whole amounts read cleaner here; only show cents when the figure has any.
    $money = fn($v) => $currency . number_format((float) $v, fmod((float) $v, 1) === 0.0 ? 0 : 2);
@endphp

<section class="border-y border-slate-200 bg-slate-50/60 py-16 dark:border-white/10 dark:bg-white/[0.02]">
    <div class="container-page">
        @if ($stats->isNotEmpty() || $revenue)
            @if ($stats->isNotEmpty())
                <dl class="grid grid-cols-2 gap-x-6 gap-y-10 sm:grid-cols-4">
                    @foreach ($stats as $stat)
                        @php $stat = (array) $stat; @endphp
                        <div class="reveal text-center">
                            <dt class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl dark:text-white"
                                x-data="{ n: 0, target: {{ (float) ($stat['value'] ?? 0) }} }" x-init="let step = target / 40;
                                let t = setInterval(() => { n = Math.min(n + step, target); if (n >= target) clearInterval(t); }, 25)">
                                {{ $stat['prefix'] ?? '' }}<span
                                    x-text="Number.isInteger(target) ? Math.round(n).toLocaleString() : n.toFixed(1)">0</span>{{ $stat['suffix'] ?? '' }}
                            </dt>
                            <dd class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ $stat['label'] ?? '' }}</dd>
                        </div>
                    @endforeach
                </dl>
            @endif

            @if ($revenue)
                @php
                    $scale = max(1, (float) $revenue['last'], (float) $revenue['current']);
                    $bars = [
                        ['label' => 'Last month', 'value' => (float) $revenue['last']],
                        ['label' => 'This month', 'value' => (float) $revenue['current']],
                    ];
                    $growth = (float) $revenue['growth'];
                    $up = $growth >= 0;
                @endphp

                <div class="reveal card mx-auto mt-14 max-w-3xl">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h3 class="font-semibold text-slate-900 dark:text-white">Revenue momentum</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                Subscription income, this month against last
                            </p>
                        </div>
                        <span @class([
                            'inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold',
                            'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' => $up,
                            'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300' => !$up,
                        ])>
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path d="{{ $up ? 'M3 17l6-6 4 4 8-8M21 7v6h-6' : 'M3 7l6 6 4-4 8 8M21 17v-6h-6' }}" />
                            </svg>
                            {{ $up ? '+' : '' }}{{ number_format($growth, 1) }}%
                        </span>
                    </div>

                    <div class="mt-8 grid gap-8 sm:grid-cols-2 sm:items-end">
                        {{-- Month-on-month bars --}}
                        <div class="flex h-44 items-end justify-center gap-10">
                            @foreach ($bars as $bar)
                                @php $pct = $bar['value'] > 0 ? max(6, $bar['value'] / $scale * 100) : 0; @endphp
                                <div class="flex h-full w-20 flex-col items-center justify-end gap-2">
                                    <span
                                        class="text-sm font-bold text-slate-900 dark:text-white">{{ $money($bar['value']) }}</span>
                                    <div class="flex w-full flex-1 items-end">
                                        <div class="w-full rounded-t-lg transition-[height] duration-1000 ease-out {{ $loop->last ? 'bg-gradient-to-t from-brand-500 to-accent-500' : 'bg-slate-200 dark:bg-white/10' }}"
                                            style="min-height: 4px" x-data="{ h: 0 }"
                                            x-init="setTimeout(() => (h = {{ round($pct, 2) }}), 150)" :style="`height: ${h}%`"></div>
                                    </div>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">{{ $bar['label'] }}</span>
                                </div>
                            @endforeach
                        </div>

                        {{-- The same figures, spelled out --}}
                        <dl class="space-y-3">
                            <div
                                class="flex items-center justify-between rounded-xl border border-slate-100 px-4 py-3 dark:border-white/5">
                                <dt class="text-sm font-medium text-slate-600 dark:text-slate-300">This month</dt>
                                <dd class="font-bold text-slate-900 dark:text-white">{{ $money($revenue['current']) }}</dd>
                            </div>
                            <div
                                class="flex items-center justify-between rounded-xl border border-slate-100 px-4 py-3 dark:border-white/5">
                                <dt class="text-sm font-medium text-slate-600 dark:text-slate-300">Last month</dt>
                                <dd class="font-bold text-slate-900 dark:text-white">{{ $money($revenue['last']) }}</dd>
                            </div>
                            <div
                                class="flex items-center justify-between rounded-xl border border-slate-100 px-4 py-3 dark:border-white/5">
                                <dt class="text-sm font-medium text-slate-600 dark:text-slate-300">Change</dt>
                                <dd @class([
                                    'font-bold',
                                    'text-emerald-600 dark:text-emerald-400' => $up,
                                    'text-rose-600 dark:text-rose-400' => !$up,
                                ])>{{ $up ? '+' : '' }}{{ number_format($growth, 2) }}%</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            @endif
        @else
            <x-landing.empty title="No stats to show yet"
                message="Institution metrics will appear here once the backend publishes them."
                icon="M3 3v18h18M7 15l4-4 3 3 5-6" />
        @endif
    </div>
</section>
