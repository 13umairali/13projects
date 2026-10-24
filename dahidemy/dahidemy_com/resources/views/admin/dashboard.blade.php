@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    @php
        // Formatting helpers (kept local — dashboard-only presentation logic).
        $money = fn ($v) => number_format((float) $v, 2);
        $whole = fn ($v) => number_format((float) $v);
        $date  = fn ($v) => filled($v) ? \Illuminate\Support\Carbon::parse($v)->format('d M Y') : '—';

        // Chart datasets.
        $monthlyRevenue    = collect(data_get($charts, 'monthly_revenue', []));
        $monthlyInstitutes = collect(data_get($charts, 'monthly_institutes', []));
        $subscriptionTrend = collect(data_get($charts, 'subscription_trend', []));
        $planDistribution  = collect(data_get($charts, 'plan_distribution', []));

        // Revenue growth (percentage vs previous month).
        $growth   = (float) data_get($statistics, 'revenue_growth', 0);
        $growthUp = $growth >= 0;

        // Year filter — current year and the previous five.
        $years        = range((int) now()->year, (int) now()->year - 5);
        $selectedYear = (int) (request('year') ?: now()->year);
    @endphp

    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div class="min-w-0">
            <h2 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Dashboard</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Platform performance across institutes, subscriptions and revenue.</p>
        </div>
        <form method="GET" action="{{ route('admin.dashboard') }}">
            <label class="sr-only" for="year">Year</label>
            <select id="year" name="year" onchange="this.form.submit()"
                class="rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-white/10 dark:bg-slate-900 dark:text-slate-200">
                @foreach ($years as $year)
                    <option value="{{ $year }}" @selected($selectedYear === $year)>{{ $year }}</option>
                @endforeach
            </select>
        </form>
    </div>

    @unless ($reachable)
        <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
            <span class="font-semibold">Live data unavailable.</span>
            The backend API could not be reached, so the figures below show zero. Check <code class="rounded bg-amber-100 px-1 dark:bg-amber-500/20">SAAS_API_URL</code>.
        </div>
    @endunless

    {{-- Stat cards --}}
    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <x-admin.stat-card label="Total institutes" :value="data_get($statistics, 'total_institutes', 0)" icon="home" tone="brand" />
        <x-admin.stat-card label="Active institutes" :value="data_get($statistics, 'active_institutes', 0)" icon="check-circle" tone="emerald" />
        <x-admin.stat-card label="Active subscriptions" :value="data_get($statistics, 'active_subscriptions', 0)" icon="bolt" tone="amber" />
        <x-admin.stat-card label="Plans" :value="data_get($statistics, 'total_plans', 0)" icon="library" tone="rose" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-[1.6fr_1fr]">
        {{-- Revenue chart --}}
        <section class="card">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="font-semibold text-slate-900 dark:text-white">Revenue</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Monthly subscription income · {{ $selectedYear }}</p>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ $money(data_get($statistics, 'total_revenue', 0)) }}</p>
                    <span @class([
                        'mt-1 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold',
                        'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' => $growthUp,
                        'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300' => ! $growthUp,
                    ])>
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path d="{{ $growthUp ? 'M3 17l6-6 4 4 8-8M21 7v6h-6' : 'M3 7l6 6 4-4 8 8M21 17v-6h-6' }}" />
                        </svg>
                        {{ $growthUp ? '+' : '' }}{{ number_format($growth, 1) }}% vs last month
                    </span>
                </div>
            </div>

            @php $maxRevenue = max(1, (float) ($monthlyRevenue->max('revenue') ?? 0)); @endphp
            <div class="mt-6 flex h-52 items-end gap-1.5">
                @forelse ($monthlyRevenue as $row)
                    @php $h = max(4, (float) data_get($row, 'revenue', 0) / $maxRevenue * 100); @endphp
                    <div class="group flex flex-1 flex-col items-center gap-2">
                        <div class="relative w-full">
                            <div class="w-full rounded-t bg-gradient-to-t from-brand-500 to-accent-500 transition-all" style="height: {{ $h }}%; min-height: 4px;"></div>
                            <span class="pointer-events-none absolute -top-6 left-1/2 z-10 -translate-x-1/2 whitespace-nowrap rounded bg-slate-900 px-1.5 py-0.5 text-[10px] text-white opacity-0 transition group-hover:opacity-100">
                                {{ $money(data_get($row, 'revenue', 0)) }}
                            </span>
                        </div>
                        <span class="text-[10px] text-slate-400">{{ \Illuminate\Support\Str::substr(data_get($row, 'month', ''), 0, 3) }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500 dark:text-slate-400">No revenue data yet.</p>
                @endforelse
            </div>

            {{-- This month / last month split --}}
            <div class="mt-6 grid grid-cols-2 gap-4 border-t border-slate-100 pt-5 dark:border-white/5">
                <div>
                    <p class="text-xs uppercase tracking-wider text-slate-400">This month</p>
                    <p class="mt-1 text-lg font-bold text-slate-900 dark:text-white">{{ $money(data_get($statistics, 'current_month_revenue', 0)) }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wider text-slate-400">Last month</p>
                    <p class="mt-1 text-lg font-bold text-slate-900 dark:text-white">{{ $money(data_get($statistics, 'last_month_revenue', 0)) }}</p>
                </div>
            </div>
        </section>

        {{-- Subscription overview --}}
        <section class="card">
            <h2 class="font-semibold text-slate-900 dark:text-white">Subscriptions</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Current lifecycle status</p>

            @php
                $subStats = [
                    ['label' => 'Active',        'value' => data_get($statistics, 'active_subscriptions', 0),      'dot' => 'bg-emerald-500', 'text' => 'text-emerald-600 dark:text-emerald-400'],
                    ['label' => 'Pending',       'value' => data_get($statistics, 'pending_subscriptions', 0),     'dot' => 'bg-amber-500',   'text' => 'text-amber-600 dark:text-amber-400'],
                    ['label' => 'Expired',       'value' => data_get($statistics, 'expired_subscriptions', 0),     'dot' => 'bg-rose-500',    'text' => 'text-rose-600 dark:text-rose-400'],
                    ['label' => 'Subscribed institutes', 'value' => data_get($statistics, 'subscription_institutes', 0), 'dot' => 'bg-brand-500', 'text' => 'text-brand-600 dark:text-brand-300'],
                ];
            @endphp
            <div class="mt-5 space-y-3">
                @foreach ($subStats as $s)
                    <div class="flex items-center justify-between rounded-xl border border-slate-100 px-4 py-3 dark:border-white/5">
                        <span class="flex items-center gap-2.5 text-sm font-medium text-slate-600 dark:text-slate-300">
                            <span class="h-2 w-2 rounded-full {{ $s['dot'] }}"></span>
                            {{ $s['label'] }}
                        </span>
                        <span class="text-lg font-bold {{ $s['text'] }}">{{ $whole($s['value']) }}</span>
                    </div>
                @endforeach
            </div>

            {{-- Institute status split --}}
            <div class="mt-4 grid grid-cols-2 gap-3">
                <div class="rounded-xl bg-emerald-50 px-4 py-3 dark:bg-emerald-500/10">
                    <p class="text-xs font-medium text-emerald-700 dark:text-emerald-300">Active institutes</p>
                    <p class="mt-1 text-xl font-bold text-emerald-700 dark:text-emerald-300">{{ $whole(data_get($statistics, 'active_institutes', 0)) }}</p>
                </div>
                <div class="rounded-xl bg-rose-50 px-4 py-3 dark:bg-rose-500/10">
                    <p class="text-xs font-medium text-rose-700 dark:text-rose-300">Inactive institutes</p>
                    <p class="mt-1 text-xl font-bold text-rose-700 dark:text-rose-300">{{ $whole(data_get($statistics, 'inactive_institutes', 0)) }}</p>
                </div>
            </div>
        </section>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        {{-- New institutes chart --}}
        <section class="card">
            <h2 class="font-semibold text-slate-900 dark:text-white">New institutes</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Sign-ups per month · {{ $selectedYear }}</p>

            @php $maxInstitutes = max(1, (float) ($monthlyInstitutes->max('institutes') ?? 0)); @endphp
            <div class="mt-6 flex h-44 items-end gap-1.5">
                @forelse ($monthlyInstitutes as $row)
                    @php $h = max(4, (float) data_get($row, 'institutes', 0) / $maxInstitutes * 100); @endphp
                    <div class="group flex flex-1 flex-col items-center gap-2">
                        <div class="relative w-full">
                            <div class="w-full rounded-t bg-gradient-to-t from-emerald-500 to-brand-500 transition-all" style="height: {{ $h }}%; min-height: 4px;"></div>
                            <span class="pointer-events-none absolute -top-6 left-1/2 z-10 -translate-x-1/2 rounded bg-slate-900 px-1.5 py-0.5 text-[10px] text-white opacity-0 transition group-hover:opacity-100">
                                {{ $whole(data_get($row, 'institutes', 0)) }}
                            </span>
                        </div>
                        <span class="text-[10px] text-slate-400">{{ \Illuminate\Support\Str::substr(data_get($row, 'month', ''), 0, 3) }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500 dark:text-slate-400">No sign-up data yet.</p>
                @endforelse
            </div>
        </section>

        {{-- Plan distribution --}}
        <section class="card">
            <h2 class="font-semibold text-slate-900 dark:text-white">Plan distribution</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Active subscriptions by plan</p>
            <div class="mt-6 space-y-4">
                @forelse ($planDistribution as $plan)
                    @php
                        $planName = data_get($plan, 'plan_name', data_get($plan, 'name', 'Plan'));
                        $planCount = data_get($plan, 'institute_count', data_get($plan, 'count', 0));
                        $planPercent = (float) data_get($plan, 'usage_percent', data_get($plan, 'percentage', 0));
                    @endphp
                    <div>
                        <div class="mb-1.5 flex items-center justify-between text-sm">
                            <span class="font-medium text-slate-700 dark:text-slate-300">{{ $planName }}</span>
                            <span class="text-slate-500 dark:text-slate-400">{{ $whole($planCount) }} · {{ number_format($planPercent, 1) }}%</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-white/10">
                            <div class="h-full rounded-full bg-gradient-to-r from-brand-500 to-accent-500" style="width: {{ min(100, max(0, $planPercent)) }}%"></div>
                        </div>
                    </div>
                @empty
                    <div class="py-8">
                        <p class="text-center text-sm text-slate-500 dark:text-slate-400">No active subscriptions yet.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        {{-- Latest subscriptions --}}
        <section class="card overflow-hidden p-0">
            <div class="border-b border-slate-100 px-5 py-4 dark:border-white/5">
                <h2 class="font-semibold text-slate-900 dark:text-white">Latest subscriptions</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">Most recent plan activations</p>
            </div>
            @if ($latestSubscriptions->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[34rem] text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-xs uppercase tracking-wider text-slate-400 dark:border-white/10">
                                <th class="px-5 py-3 font-semibold">Institute</th>
                                <th class="px-5 py-3 font-semibold">Plan</th>
                                <th class="px-5 py-3 text-right font-semibold">Amount</th>
                                <th class="px-5 py-3 text-right font-semibold">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                            @foreach ($latestSubscriptions as $row)
                                <tr class="transition hover:bg-slate-50/70 dark:hover:bg-white/[0.02]">
                                    <td class="px-5 py-3">
                                        <p class="font-semibold text-slate-900 dark:text-white">{{ data_get($row, 'institute.name', data_get($row, 'institute_name', '—')) }}</p>
                                        <p class="text-xs text-slate-400">{{ data_get($row, 'institute.email', data_get($row, 'email', '')) }}</p>
                                    </td>
                                    <td class="px-5 py-3 text-slate-600 dark:text-slate-300">{{ data_get($row, 'plan.name', data_get($row, 'plan_name', '—')) }}</td>
                                    <td class="px-5 py-3 text-right font-medium text-slate-700 dark:text-slate-200">{{ $money(data_get($row, 'amount_paid', data_get($row, 'amount', 0))) }}</td>
                                    <td class="px-5 py-3 text-right text-slate-500 dark:text-slate-400">{{ $date(data_get($row, 'created_at', data_get($row, 'start_date'))) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <x-admin.empty title="No subscriptions yet" subtitle="Recent plan activations will appear here." />
            @endif
        </section>

        {{-- Expiring subscriptions --}}
        <section class="card overflow-hidden p-0">
            <div class="border-b border-slate-100 px-5 py-4 dark:border-white/5">
                <h2 class="font-semibold text-slate-900 dark:text-white">Expiring soon</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">Subscriptions nearing renewal</p>
            </div>
            @if ($expiringSubscriptions->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[34rem] text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-xs uppercase tracking-wider text-slate-400 dark:border-white/10">
                                <th class="px-5 py-3 font-semibold">Institute</th>
                                <th class="px-5 py-3 font-semibold">Plan</th>
                                <th class="px-5 py-3 text-right font-semibold">Ends</th>
                                <th class="px-5 py-3 text-right font-semibold">Days left</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                            @foreach ($expiringSubscriptions as $row)
                                @php
                                    $endDate  = data_get($row, 'end_date', data_get($row, 'ends_at', data_get($row, 'expiry_date')));
                                    $daysLeft = data_get($row, 'days_left', data_get($row, 'days_remaining'));
                                    if ($daysLeft === null && filled($endDate)) {
                                        $daysLeft = (int) round(now()->startOfDay()->diffInDays(\Illuminate\Support\Carbon::parse($endDate)->startOfDay(), false));
                                    }
                                    $daysLeft = (int) $daysLeft;
                                @endphp
                                <tr class="transition hover:bg-slate-50/70 dark:hover:bg-white/[0.02]">
                                    <td class="px-5 py-3">
                                        <p class="font-semibold text-slate-900 dark:text-white">{{ data_get($row, 'institute.name', data_get($row, 'institute_name', '—')) }}</p>
                                        <p class="text-xs text-slate-400">{{ data_get($row, 'institute.email', data_get($row, 'email', '')) }}</p>
                                    </td>
                                    <td class="px-5 py-3 text-slate-600 dark:text-slate-300">{{ data_get($row, 'plan.name', data_get($row, 'plan_name', '—')) }}</td>
                                    <td class="px-5 py-3 text-right text-slate-500 dark:text-slate-400">{{ $date($endDate) }}</td>
                                    <td class="px-5 py-3 text-right">
                                        <span @class([
                                            'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold',
                                            'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300' => $daysLeft <= 7,
                                            'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300' => $daysLeft > 7 && $daysLeft <= 30,
                                            'bg-slate-100 text-slate-600 dark:bg-white/5 dark:text-slate-300' => $daysLeft > 30,
                                        ])>
                                            {{ $daysLeft }} {{ \Illuminate\Support\Str::plural('day', $daysLeft) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <x-admin.empty title="Nothing expiring soon" subtitle="Subscriptions close to renewal will appear here." />
            @endif
        </section>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        {{-- Recent payments --}}
        <section class="card overflow-hidden p-0">
            <div class="border-b border-slate-100 px-5 py-4 dark:border-white/5">
                <h2 class="font-semibold text-slate-900 dark:text-white">Recent payments</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">Latest transactions received</p>
            </div>
            @if ($recentPayments->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[34rem] text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-xs uppercase tracking-wider text-slate-400 dark:border-white/10">
                                <th class="px-5 py-3 font-semibold">Institute</th>
                                <th class="px-5 py-3 font-semibold">Method</th>
                                <th class="px-5 py-3 text-right font-semibold">Amount</th>
                                <th class="px-5 py-3 text-right font-semibold">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                            @foreach ($recentPayments as $row)
                                <tr class="transition hover:bg-slate-50/70 dark:hover:bg-white/[0.02]">
                                    <td class="px-5 py-3 font-semibold text-slate-900 dark:text-white">{{ data_get($row, 'institute.name', data_get($row, 'institute_name', '—')) }}</td>
                                    <td class="px-5 py-3">
                                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium capitalize text-slate-600 dark:bg-white/5 dark:text-slate-300">
                                            {{ str_replace('_', ' ', data_get($row, 'payment_method', data_get($row, 'method', '—'))) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-right font-medium text-emerald-600 dark:text-emerald-400">{{ $money(data_get($row, 'amount_paid', data_get($row, 'amount', 0))) }}</td>
                                    <td class="px-5 py-3 text-right text-slate-500 dark:text-slate-400">{{ $date(data_get($row, 'created_at', data_get($row, 'paid_at'))) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <x-admin.empty title="No payments yet" subtitle="Transactions received from institutes will appear here." />
            @endif
        </section>

        {{-- Top paying institutes --}}
        <section class="card overflow-hidden p-0">
            <div class="border-b border-slate-100 px-5 py-4 dark:border-white/5">
                <h2 class="font-semibold text-slate-900 dark:text-white">Top paying institutes</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">Highest lifetime revenue</p>
            </div>
            @if ($topInstitutes->isNotEmpty())
                <div class="divide-y divide-slate-100 dark:divide-white/5">
                    @foreach ($topInstitutes as $i => $row)
                        <div class="flex items-center gap-3 px-5 py-3.5 transition hover:bg-slate-50/70 dark:hover:bg-white/[0.02]">
                            <span @class([
                                'grid h-8 w-8 flex-none place-items-center rounded-lg text-sm font-bold',
                                'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300' => $i === 0,
                                'bg-slate-100 text-slate-500 dark:bg-white/5 dark:text-slate-400' => $i !== 0,
                            ])>{{ $i + 1 }}</span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-semibold text-slate-900 dark:text-white">{{ data_get($row, 'name', data_get($row, 'institute_name', data_get($row, 'institute.name', '—'))) }}</p>
                                <p class="truncate text-xs text-slate-400">{{ data_get($row, 'email', data_get($row, 'domain', '')) }}</p>
                            </div>
                            <span class="flex-none font-bold text-slate-900 dark:text-white">{{ $money(data_get($row, 'total_paid', data_get($row, 'total_revenue', data_get($row, 'total_amount', data_get($row, 'amount', 0))))) }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <x-admin.empty title="No revenue yet" subtitle="Institutes ranked by revenue will appear here." />
            @endif
        </section>
    </div>
@endsection
