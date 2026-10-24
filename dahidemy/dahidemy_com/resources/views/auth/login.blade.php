@extends('layouts.auth')

@section('content')
    @php $activated = $activated ?? true; @endphp

    <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
            {{ $activated ? 'Welcome back' : 'Activate your panel' }}
        </h1>
        <p class="mt-1.5 text-sm text-slate-500 dark:text-slate-400">
            {{ $activated
                ? 'Sign in to your SaaS admin dashboard.'
                : 'Set up the administrator account to start using the dashboard.' }}
        </p>
    </div>

    @if (session('success'))
        <div
            class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div
            class="mt-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300">
            {{ session('error') }}
        </div>
    @endif

    @unless ($activated)
        {{-- First run: no SaaS administrator exists on the backend yet. --}}
        <div class="mt-8 rounded-2xl border border-amber-200 bg-amber-50 p-5 dark:border-amber-500/30 dark:bg-amber-500/10">
            <div class="flex items-start gap-3">
                <span
                    class="grid h-9 w-9 flex-none place-items-center rounded-lg bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-300">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path
                            d="M12 15a2 2 0 100-4 2 2 0 000 4zM12 15v3M6 10V7a6 6 0 1112 0v3M5 10h14a2 2 0 012 2v7a2 2 0 01-2 2H5a2 2 0 01-2-2v-7a2 2 0 012-2z" />
                    </svg>
                </span>
                <div>
                    <h2 class="text-sm font-semibold text-amber-800 dark:text-amber-200">No administrator account yet</h2>
                    <p class="mt-1 text-sm text-amber-700/90 dark:text-amber-300/80">
                        Click activate to create the default SaaS admin on the backend. You can sign in with your SaaS admin
                        credentials once it's done.
                    </p>
                </div>
            </div>
        </div>

        <form action="{{ route('activation.activate') }}" method="POST" class="mt-4">
            @csrf
            <button type="submit" class="btn-primary w-full">Activate SaaS account</button>
        </form>
    @else
        @php
            // Dev convenience only. Prefilling real admin credentials is fine on a
            // local machine and a handed-out admin login in production, so the
            // values exist ONLY when app.env is local.
            $devEmail = app()->environment('local') ? '' : '';
            $devPassword = app()->environment('local') ? '' : '';
        @endphp

        <form action="{{ route('login.store') }}" method="POST" class="mt-8 space-y-4">
            @csrf
            <x-landing.field name="email" type="email" label="Email" :value="old('email', $devEmail)"
                placeholder="you@school.com" required />

            <div>
                <div class="mb-1.5 flex items-center justify-between">
                    <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Password</label>
                    <a href="{{ route('password.request') }}"
                        class="text-xs font-semibold text-brand-600 hover:underline dark:text-brand-400">Forgot?</a>
                </div>
                <input id="password" name="password" type="password" value="{{ $devPassword }}" required placeholder="••••••••"
                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30 dark:border-white/10 dark:bg-slate-900 dark:text-white">
                @error('password')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                <input type="checkbox" name="remember" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                Remember me
            </label>

            <button type="submit" class="btn-primary w-full">Sign in</button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-500 dark:text-slate-400">
            New institution?
            <a href="{{ route('pricing') }}" class="font-semibold text-brand-600 hover:underline dark:text-brand-400">Get
                started</a>
        </p>
    @endunless
@endsection
