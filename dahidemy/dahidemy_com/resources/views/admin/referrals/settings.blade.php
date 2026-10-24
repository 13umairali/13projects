@extends('layouts.admin')

@section('title', 'Referral reward settings')

@section('content')
    <x-admin.page-header title="Referral reward settings"
        subtitle="What each referrer role earns when their referral is approved. Add a role that isn't listed yet, or edit an existing one.">
        <a href="{{ route('admin.referrals.index') }}" class="btn-ghost px-4 py-2.5">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Back to referrals
        </a>
    </x-admin.page-header>

    @unless ($reachable)
        <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
            <span class="font-semibold">Live data unavailable.</span>
            The backend API could not be reached — check <code class="rounded bg-amber-100 px-1 dark:bg-amber-500/20">SAAS_API_URL</code> and that you are signed in.
        </div>
    @endunless

    {{-- Real role names in use platform-wide, including any custom roles
         individual institutes have created themselves - shown as
         suggestions so the admin can see what actually exists instead of
         typing blind, while still allowing any value in case a role
         hasn't been created yet. --}}
    <datalist id="knownRoles">
        @foreach ($roleOptions as $roleName)
            <option value="{{ $roleName }}"></option>
        @endforeach
    </datalist>

    {{-- Existing rules --}}
    <div class="card overflow-hidden p-0 mb-8">
        @if (count($settings))
            <div class="overflow-x-auto">
                <table class="w-full min-w-[48rem] text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-xs uppercase tracking-wider text-slate-400 dark:border-white/10">
                            <th class="px-5 py-3 font-semibold">Referrer Role</th>
                            <th class="px-5 py-3 font-semibold">Reward Type</th>
                            <th class="px-5 py-3 font-semibold">Reward Value</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                            <th class="px-5 py-3 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                        @foreach ($settings as $row)
                            @php $formId = 'setting-form-'.($row['id'] ?? 'new'); @endphp
                            <tr class="transition hover:bg-slate-50/70 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-3">
                                    <input type="text" name="referrer_role" form="{{ $formId }}" value="{{ $row['referrer_role'] ?? '' }}"
                                        list="knownRoles"
                                        class="w-32 rounded-lg border border-slate-200 px-2 py-1.5 text-sm dark:border-white/10 dark:bg-transparent" required>
                                </td>
                                <td class="px-5 py-3">
                                    <select name="reward_type" form="{{ $formId }}" class="rounded-lg border border-slate-200 px-2 py-1.5 text-sm dark:border-white/10 dark:bg-transparent">
                                        @foreach (['days' => 'Extra Days', 'cash' => 'Cash', 'points' => 'Points', 'percentage' => '% of Package Price'] as $value => $label)
                                            <option value="{{ $value }}" {{ ($row['reward_type'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-5 py-3">
                                    <input type="number" step="0.01" min="0" name="reward_value" form="{{ $formId }}" value="{{ $row['reward_value'] ?? 0 }}"
                                        class="w-24 rounded-lg border border-slate-200 px-2 py-1.5 text-sm dark:border-white/10 dark:bg-transparent" required>
                                </td>
                                <td class="px-5 py-3">
                                    <select name="status" form="{{ $formId }}" class="rounded-lg border border-slate-200 px-2 py-1.5 text-sm dark:border-white/10 dark:bg-transparent">
                                        <option value="1" {{ ($row['status'] ?? true) ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ ! ($row['status'] ?? true) ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </td>
                                <td class="px-5 py-3 text-right whitespace-nowrap">
                                    {{-- This form has no visible fields of its own - every input above
                                         points at it via the form="..." attribute, which is the
                                         HTML-valid way to associate inputs with a form without the
                                         form needing to be a structural ancestor of the <td>s. --}}
                                    <form id="{{ $formId }}" method="POST" action="{{ route('admin.referrals.settings.update', $row['id'] ?? null) }}">
                                        @csrf
                                        @method('PUT')
                                    </form>
                                    <div class="flex justify-end gap-2">
                                        <button type="submit" form="{{ $formId }}" class="btn-primary px-3 py-2 text-xs">Save</button>
                                        <form method="POST" action="{{ route('admin.referrals.settings.destroy', $row['id'] ?? null) }}"
                                            onsubmit="return confirm('Delete this reward rule?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-ghost px-3 py-2 text-xs text-rose-600">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <x-admin.empty title="No reward rules yet"
                subtitle="Add one below for any role - institute, student, parent, teacher, accountant, staff, or any other role in use." />
        @endif
    </div>

    {{-- Add new rule --}}
    <div class="card">
        <h3 class="mb-4 text-base font-semibold text-slate-900 dark:text-white">Add a reward rule</h3>
        <form method="POST" action="{{ route('admin.referrals.settings.store') }}" class="grid gap-4 sm:grid-cols-4">
            @csrf
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-500">Referrer Role</label>
                <input type="text" name="referrer_role" placeholder="e.g. student, teacher, institute" list="knownRoles"
                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-transparent" required>
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-500">Reward Type</label>
                <select name="reward_type" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-transparent">
                    <option value="days">Extra Days</option>
                    <option value="cash">Cash</option>
                    <option value="points">Points</option>
                    <option value="percentage">% of Package Price</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-500">Reward Value</label>
                <input type="number" step="0.01" min="0" name="reward_value" placeholder="e.g. 30, 50, 100, or 10 for 10%"
                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-transparent" required>
            </div>
            <div class="flex items-end">
                <button type="submit" class="btn-primary w-full px-4 py-2.5">Add Rule</button>
            </div>
        </form>
    </div>
@endsection