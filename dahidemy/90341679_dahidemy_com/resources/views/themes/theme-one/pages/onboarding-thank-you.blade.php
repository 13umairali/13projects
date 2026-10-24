@extends('themes.theme-one.layouts.app')

@section('content')
    <section class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-100 py-16">
        <div class="max-w-3xl mx-auto px-4">

            <div class="text-center mb-10">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-r from-green-500 to-emerald-500 shadow-xl mb-5">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                </div>

                <h1 class="text-4xl font-black text-slate-800">
                    You're All Set!
                </h1>
                <p class="text-slate-500 mt-3">
                    "{{ $result['institute_name'] }}" has been created on the {{ $result['plan_name'] }} plan.
                </p>
            </div>

            <div class="bg-white/90 backdrop-blur-xl border border-white shadow-2xl rounded-[35px] p-8 md:p-12">

                <div class="flex items-start gap-3 bg-amber-50 border border-amber-200 rounded-2xl p-4 mb-8">
                    <span class="text-amber-600 text-xl">⚠️</span>
                    <p class="text-sm text-amber-800">
                        Save your password now — for security, it's only ever shown here, once. It's also been emailed to your admin address.
                    </p>
                </div>

                <div class="space-y-5">
                    <div class="flex items-center justify-between gap-4 border-b border-slate-100 pb-4">
                        <span class="text-slate-500 text-sm">Institute Name</span>
                        <span class="font-semibold text-slate-800">{{ $result['institute_name'] }}</span>
                    </div>

                    <div class="flex items-center justify-between gap-4 border-b border-slate-100 pb-4">
                        <span class="text-slate-500 text-sm">Your Website</span>
                        <a href="{{ 'https://'.$result['institute_domain'] }}" target="_blank"
                            class="font-semibold text-indigo-600 hover:underline break-all">
                            {{ $result['institute_domain'] }}
                        </a>
                    </div>

                    <div class="flex items-center justify-between gap-4 border-b border-slate-100 pb-4">
                        <span class="text-slate-500 text-sm">Login Email</span>
                        <span class="font-semibold text-slate-800 break-all">{{ $result['login_email'] }}</span>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-500 text-sm">Password</span>
                        <span class="font-mono font-semibold text-slate-800 bg-slate-100 px-3 py-1.5 rounded-lg" id="onboardingPassword">
                            {{ $result['login_password'] }}
                        </span>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-4 mt-10">
                    <a href="{{ url('/login') }}"
                        class="flex-1 text-center px-8 py-4 rounded-full bg-gradient-to-r from-purple-600 to-pink-500 text-white font-bold shadow-xl hover:scale-105 transition">
                        Log In Now
                    </a>
                    <a href="{{ 'https://'.$result['institute_domain'] }}" target="_blank"
                        class="flex-1 text-center px-8 py-4 rounded-full border-2 border-slate-200 text-slate-700 font-bold hover:border-slate-300 transition">
                        Visit Your Website
                    </a>
                </div>
            </div>

            <p class="text-center text-sm text-slate-400 mt-8">
                Refreshing or revisiting this page won't show your password again — keep it somewhere safe.
            </p>
        </div>
    </section>
@endsection