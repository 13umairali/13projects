@extends('layouts.auth')

@section('content')
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Reset your password</h1>
        <p class="mt-1.5 text-sm text-slate-500 dark:text-slate-400">Enter your email and we’ll send you a reset link.</p>
    </div>

    @if (session('success'))
        <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('password.request') }}" method="POST" class="mt-8 space-y-4">
        @csrf
        <x-landing.field name="email" type="email" label="Email" :value="old('email')" placeholder="you@school.com" required />
        <button type="submit" class="btn-primary w-full">Send reset link</button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500 dark:text-slate-400">
        Remembered it?
        <a href="{{ route('login') }}" class="font-semibold text-brand-600 hover:underline dark:text-brand-400">Back to sign in</a>
    </p>
@endsection
