@extends('layouts.landing')

@section('content')
    <section class="relative overflow-hidden pt-32 pb-24 sm:pt-40">
        <div class="hero-aurora absolute inset-0 -z-10"></div>
        <div class="container-page max-w-2xl">
            <div class="reveal text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-500/10">
                    <svg class="h-8 w-8 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>

                <span class="eyebrow mt-6 inline-block">Registration complete</span>
                <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl dark:text-white">
                    You're all set!
                </h1>
                <p class="mt-3 text-slate-600 dark:text-slate-400">
                    "{{ $result['institute_name'] }}" has been submitted and will be reviewed shortly.
                </p>
            </div>

            <div class="card reveal mt-10">
                <div class="flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-500/30 dark:bg-amber-500/10">
                    <span class="text-lg">⚠️</span>
                    <p class="text-sm text-amber-800 dark:text-amber-300">
                        Save your password now — for security, it's only ever shown here, once.
                    </p>
                </div>

                <dl class="mt-6 divide-y divide-slate-100 dark:divide-white/10">
                    <div class="flex items-center justify-between gap-4 py-3">
                        <dt class="text-sm text-slate-500 dark:text-slate-400">Institution name</dt>
                        <dd class="font-semibold text-slate-900 dark:text-white">{{ $result['institute_name'] }}</dd>
                    </div>

                    @if ($result['institute_domain'])
                        <div class="flex items-center justify-between gap-4 py-3">
                            <dt class="text-sm text-slate-500 dark:text-slate-400">Your website</dt>
                            <dd>
                                <a href="{{ 'https://'.$result['institute_domain'] }}" target="_blank"
                                    class="font-semibold text-brand-600 hover:underline dark:text-brand-400 break-all">
                                    {{ $result['institute_domain'] }}
                                </a>
                            </dd>
                        </div>
                    @endif

                    <div class="flex items-center justify-between gap-4 py-3">
                        <dt class="text-sm text-slate-500 dark:text-slate-400">Login email</dt>
                        <dd class="font-semibold text-slate-900 dark:text-white break-all">{{ $result['login_email'] }}</dd>
                    </div>

                    <div class="flex items-center justify-between gap-4 py-3">
                        <dt class="text-sm text-slate-500 dark:text-slate-400">Password</dt>
                        <dd class="rounded-lg bg-slate-100 px-3 py-1.5 font-mono font-semibold text-slate-900 dark:bg-white/10 dark:text-white">
                            {{ $result['login_password'] }}
                        </dd>
                    </div>
                </dl>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    @if ($result['institute_domain'])
                        <a href="{{ 'https://'.$result['institute_domain'].'/login' }}" class="btn-primary flex-1 text-center">
                            Log In Now
                        </a>
                        <a href="{{ 'https://'.$result['institute_domain'] }}" target="_blank"
                            class="flex-1 rounded-xl border border-slate-200 px-6 py-3 text-center text-sm font-semibold text-slate-700 transition hover:border-slate-300 dark:border-white/10 dark:text-slate-200">
                            Visit Your Website
                        </a>
                    @else
                        <a href="{{ route('register.show') }}" class="btn-primary flex-1 text-center">
                            Back to Registration
                        </a>
                    @endif
                </div>
            </div>

            <p class="reveal mt-6 text-center text-sm text-slate-400">
                Refreshing or revisiting this page won't show your password again — keep it somewhere safe.
            </p>
        </div>
    </section>
@endsection