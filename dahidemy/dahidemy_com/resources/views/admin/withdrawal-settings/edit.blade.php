@extends('layouts.admin')

@section('title', 'Withdrawal settings')

@section('content')
    <x-admin.page-header title="Withdrawal settings"
        subtitle="The platform-wide defaults every institute inherits, unless they've set their own tighter override for their own users." />

    @unless ($reachable)
        <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
            <span class="font-semibold">Live data unavailable.</span>
            The backend API could not be reached — check <code class="rounded bg-amber-100 px-1 dark:bg-amber-500/20">SAAS_API_URL</code> and that you are signed in.
        </div>
    @endunless

    <div class="card max-w-2xl">
        <form method="POST" action="{{ route('admin.withdrawal-settings.update') }}">
            @csrf
            @method('PUT')

            <div class="grid gap-4 sm:grid-cols-2 mb-6">
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-500">Minimum Withdrawal Amount ($)</label>
                    <input type="number" step="0.01" min="0" name="min_amount"
                        value="{{ old('min_amount', $setting['min_amount'] ?? '') }}"
                        placeholder="0.00 (no minimum)"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-transparent">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-500">Maximum Withdrawal Amount ($)</label>
                    <input type="number" step="0.01" min="0" name="max_amount"
                        value="{{ old('max_amount', $setting['max_amount'] ?? '') }}"
                        placeholder="Leave blank for unlimited"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-transparent">
                </div>
            </div>

            <div class="mb-6">
                <label class="mb-2 block text-xs font-semibold text-slate-500">Enabled Payout Methods</label>
                <p class="mb-3 text-xs text-slate-400">Leave all unchecked to allow every method by default.</p>
                <div class="flex flex-wrap gap-4">
                    @php $currentMethods = old('enabled_methods', $setting['enabled_methods'] ?? []) ?? []; @endphp
                    @foreach ($allMethods as $value => $label)
                        <label class="inline-flex items-center gap-2 text-sm">
                            <input type="checkbox" name="enabled_methods[]" value="{{ $value }}"
                                {{ in_array($value, $currentMethods) ? 'checked' : '' }}
                                class="rounded border-slate-300">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>

            <button type="submit" class="btn-primary px-5 py-2.5">Save Settings</button>
        </form>
    </div>

    <p class="mt-4 max-w-2xl text-xs text-slate-400">
        An institute can only make these limits tighter for its own users (a lower maximum, a higher minimum, fewer
        methods) — never looser than what's set here.
    </p>
@endsection