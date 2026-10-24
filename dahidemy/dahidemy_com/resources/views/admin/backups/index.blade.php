@extends('layouts.admin')

@section('title', 'Database backups')

@section('content')
    <x-admin.page-header title="Database backups"
        subtitle="Run a manual backup now, or configure automatic daily backups and their destination on the settings page." />

    @unless ($reachable)
        <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
            <span class="font-semibold">Live data unavailable.</span>
            The backend API could not be reached — check <code class="rounded bg-amber-100 px-1 dark:bg-amber-500/20">SAAS_API_URL</code> and that you are signed in.
        </div>
    @endunless

    <div class="card mb-6 flex items-center justify-between">
        <div>
            <p class="font-semibold text-slate-900 dark:text-white">Run a backup now</p>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                @if ($remoteConfigured)
                    Stores to the configured S3 / R2 destination.
                @else
                    No remote destination configured — <a href="{{ route('admin.settings.index') }}" class="text-brand-600 underline">configure one</a>, or this will save to the server's local disk.
                @endif
            </p>
        </div>
        <form method="POST" action="{{ route('admin.backups.run') }}">
            @csrf
            <button type="submit" class="btn-primary px-4 py-2.5">Run backup now</button>
        </form>
    </div>

    <div class="card overflow-hidden p-0">
        @if (!empty($backups))
            <div class="overflow-x-auto">
                <table class="w-full min-w-[36rem] text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-xs uppercase tracking-wider text-slate-400 dark:border-white/10">
                            <th class="px-5 py-3 font-semibold">File</th>
                            <th class="px-5 py-3 font-semibold">Size</th>
                            <th class="px-5 py-3 font-semibold">Stored</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                        @foreach ($backups as $backup)
                            <tr>
                                <td class="px-5 py-3 font-mono text-xs text-slate-700 dark:text-slate-300">{{ $backup['name'] ?? '—' }}</td>
                                <td class="px-5 py-3 text-slate-500 dark:text-slate-400">{{ isset($backup['size']) ? number_format($backup['size'] / 1024, 1).' KB' : '—' }}</td>
                                <td class="px-5 py-3 text-slate-500 dark:text-slate-400">
                                    {{ !empty($backup['last_modified']) ? \Illuminate\Support\Carbon::createFromTimestamp($backup['last_modified'])->format('d M Y H:i') : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <x-admin.empty title="No remote backups yet"
                subtitle="Run a manual backup, or wait for the daily scheduled one, once a remote destination is configured." />
        @endif
    </div>
@endsection
