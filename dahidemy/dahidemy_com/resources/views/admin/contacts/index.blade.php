@extends('layouts.admin')

@section('title', 'Contact messages')

@section('content')
    @php
        // Sortable column header — toggles asc/desc and resets to page 1. The
        // backend does the sorting (sort_by / sort_order), so this only ever
        // hands it a column from its own allow-list.
        $sortLink = function (string $column, string $label) use ($filters) {
            $isActive = $filters['sort_by'] === $column;
            $nextOrder = $isActive && $filters['sort_order'] === 'asc' ? 'desc' : 'asc';
            $url = request()->fullUrlWithQuery([
                'sort_by' => $column,
                'sort_order' => $nextOrder,
                'page' => 1,
            ]);
            $arrow = $isActive ? ($filters['sort_order'] === 'asc' ? '&uarr;' : '&darr;') : '';

            return '<a href="' .
                $url .
                '" class="inline-flex items-center gap-1 transition hover:text-brand-600">' .
                e($label) .
                ' <span class="text-[10px]">' .
                $arrow .
                '</span></a>';
        };
    @endphp

    <x-admin.page-header title="Contact messages"
        subtitle="Enquiries submitted through the contact form on the public site." />

    <x-admin.search :action="route('admin.contacts.index')" :value="$filters['search']" placeholder="Search name, email, phone, subject…" :per-page="$filters['per_page']"
        :hidden="['sort_by' => $filters['sort_by'], 'sort_order' => $filters['sort_order']]" />

    @unless ($reachable)
        <div
            class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
            <span class="font-semibold">Live data unavailable.</span>
            The backend API could not be reached — check <code
                class="rounded bg-amber-100 px-1 dark:bg-amber-500/20">SAAS_API_URL</code> and that you are signed in.
        </div>
    @endunless

    <div class="card overflow-hidden p-0">
        @if ($contacts->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full min-w-[52rem] text-left text-sm">
                    <thead>
                        <tr
                            class="border-b border-slate-200 text-xs uppercase tracking-wider text-slate-400 dark:border-white/10">
                            <th class="px-5 py-3 font-semibold">{!! $sortLink('name', 'From') !!}</th>
                            <th class="px-5 py-3 font-semibold">{!! $sortLink('phone', 'Phone') !!}</th>
                            <th class="px-5 py-3 font-semibold">{!! $sortLink('subject', 'Subject') !!}</th>
                            <th class="px-5 py-3 font-semibold">Message</th>
                            <th class="px-5 py-3 font-semibold">{!! $sortLink('created_at', 'Received') !!}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                        @foreach ($contacts as $contact)
                            @php
                                $name = $contact['name'] ?? '—';
                                $email = $contact['email'] ?? '';
                                $message = (string) ($contact['message'] ?? '');
                                $subject = $contact['subject'] ?? '—';
                                $isNew = (int) ($contact['isSeen'] ?? 0) === 0;
                                $received = filled($contact['created_at'] ?? null)
                                    ? \Illuminate\Support\Carbon::parse($contact['created_at'])
                                    : null;
                            @endphp
                            <tr class="align-top transition hover:bg-slate-50/70 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-3">
                                    <div class="flex items-start gap-2">
                                        <div class="min-w-0">
                                            <p class="truncate font-semibold text-slate-900 dark:text-white">{{ $name }}
                                            </p>
                                            @if ($email)
                                                <a href="mailto:{{ $email }}"
                                                    class="truncate text-xs text-slate-400 transition hover:text-brand-600">{{ $email }}</a>
                                            @endif
                                        </div>
                                        @if ($isNew)
                                            <span
                                                class="mt-0.5 inline-flex flex-none items-center rounded-full bg-brand-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-brand-700 ring-1 ring-inset ring-brand-600/20 dark:bg-brand-500/10 dark:text-brand-300 dark:ring-brand-400/20">
                                                New
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-3 whitespace-nowrap text-slate-600 dark:text-slate-300">
                                    {{ $contact['phone'] ?? '—' }}
                                </td>
                                <td class="px-5 py-3 text-slate-600 dark:text-slate-300">{{ $subject }}</td>

                                {{-- The whole body already arrives in the list payload, so reading a
                                     message costs no extra request. --}}
                                <td class="max-w-xs px-5 py-3" x-data="{ open: false }">
                                    <p class="truncate text-slate-600 dark:text-slate-300">{{ $message ?: '—' }}</p>
                                    @if ($message !== '')
                                        <button type="button" @click="open = true"
                                            class="mt-1 text-xs font-semibold text-brand-600 transition hover:underline dark:text-brand-300">
                                            Read message
                                        </button>

                                        <div x-show="open" x-cloak x-transition.opacity
                                            @keydown.escape.window="open = false"
                                            class="fixed inset-0 z-50 grid place-items-center bg-slate-900/50 p-4">
                                            <div @click.outside="open = false"
                                                class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-6 shadow-xl dark:border-white/10 dark:bg-slate-900">
                                                <div class="flex items-start justify-between gap-4">
                                                    <div class="min-w-0">
                                                        <h3 class="truncate font-semibold text-slate-900 dark:text-white">
                                                            {{ $subject }}
                                                        </h3>
                                                        <p class="mt-0.5 truncate text-sm text-slate-500 dark:text-slate-400">
                                                            {{ $name }}@if ($email) · {{ $email }} @endif
                                                        </p>
                                                    </div>
                                                    <button type="button" @click="open = false"
                                                        class="grid h-8 w-8 flex-none place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-white/5">
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor"
                                                            stroke-width="2" viewBox="0 0 24 24">
                                                            <path d="M18 6L6 18M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </div>

                                                <p
                                                    class="mt-4 max-h-72 overflow-y-auto whitespace-pre-line text-sm leading-relaxed text-slate-700 dark:text-slate-300">
                                                    {{ $message }}
                                                </p>

                                                <div
                                                    class="mt-5 flex items-center justify-between gap-3 border-t border-slate-100 pt-4 dark:border-white/5">
                                                    <span class="text-xs text-slate-400">
                                                        {{ $received?->format('d M Y · H:i') ?? '' }}
                                                    </span>
                                                    @if ($email)
                                                        <a href="mailto:{{ $email }}?subject={{ rawurlencode('Re: ' . $subject) }}"
                                                            class="btn-primary px-4 py-2">Reply by email</a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-5 py-3 whitespace-nowrap text-slate-500 dark:text-slate-400">
                                    {{ $received?->format('d M Y') ?? '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <x-admin.empty title="No messages" :subtitle="filled($filters['search'])
                ? 'No messages match your search.'
                : 'Enquiries from the public contact form will appear here.'" />
        @endif
    </div>

    @if ($contacts->hasPages())
        <div class="mt-5">
            {{ $contacts->onEachSide(1)->links() }}
        </div>
    @endif

    @if ($contacts->isNotEmpty())
        <p class="mt-3 text-xs text-slate-400 dark:text-slate-500">
            Showing {{ $contacts->firstItem() }}–{{ $contacts->lastItem() }} of {{ $contacts->total() }} messages
        </p>
    @endif
@endsection
