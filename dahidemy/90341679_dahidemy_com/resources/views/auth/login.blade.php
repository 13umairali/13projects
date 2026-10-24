@extends('themes.' . config('frontend.active_theme', 'theme-three') . '.layouts.app')

@section('title', _lang('Login'))

@section('content')
    {{-- No custom header/footer here at all — this page now uses the exact
         same partials/header.blade.php and partials/footer.blade.php as
         every other page, via the shared layout above. Only the form
         panel itself is page-specific. Colors come from the same
         primary_color/secondary_color CSS variables the layout's
         styles.blade.php already sets, so nothing needs re-resolving here. --}}
    <section class="relative px-4 py-14 md:py-20" style="background: var(--paper-100);">
        <div class="max-w-5xl mx-auto grid grid-cols-1 lg:grid-cols-2 overflow-hidden border bg-white" style="border-radius: 8px; border-color: var(--line-200);">

            {{-- LEFT: brand panel --}}
            <div class="relative hidden lg:flex flex-col justify-between p-14 text-white overflow-hidden" style="background: var(--navy-800);">
                <div class="relative z-10">
                    <span class="inline-flex items-center gap-2 text-xs font-semibold px-4 py-2 border" style="border-radius: 4px; border-color: rgba(255,255,255,.25); background: rgba(255,255,255,.08);">
                        <i class="fa fa-shield"></i> {{ _lang('Secure Access') }}
                    </span>

                    <h2 class="mt-8 text-4xl xl:text-[2.75rem] font-display leading-tight">
                        {{ _lang('Welcome back to your institute dashboard') }}
                    </h2>

                    <p class="mt-5 text-white/70 leading-7 max-w-md">
                        {{ _lang('Manage academics, attendance, exams, fees and communication — all from one place.') }}
                    </p>
                </div>

                <div class="relative z-10 space-y-4 mt-10">
                    <div class="flex items-center gap-4 border p-4" style="border-radius: 4px; border-color: rgba(255,255,255,.15); background: rgba(255,255,255,.06);">
                        <span class="w-10 h-10 flex items-center justify-center shrink-0" style="border-radius: 4px; background: rgba(255,255,255,.1);"><i class="fa fa-lock"></i></span>
                        <p class="text-sm text-white/90">{{ _lang('Login with your Email / Phone and Password.') }}</p>
                    </div>
                    <div class="flex items-center gap-4 border p-4" style="border-radius: 4px; border-color: rgba(255,255,255,.15); background: rgba(255,255,255,.06);">
                        <span class="w-10 h-10 flex items-center justify-center shrink-0" style="border-radius: 4px; background: rgba(255,255,255,.1);"><i class="fa fa-th-large"></i></span>
                        <p class="text-sm text-white/90">{{ _lang('Your personal dashboard will appear right after login.') }}</p>
                    </div>
                </div>
            </div>

            {{-- RIGHT: form panel --}}
            <div class="flex items-center p-6 sm:p-10 lg:p-14">
                <div class="w-full max-w-md mx-auto">

                    <div class="mb-9">
                        <h1 class="text-3xl sm:text-4xl font-display" style="color: var(--ink-900);">
                            {{ _lang('Sign In') }}
                        </h1>
                        <p class="mt-2" style="color: var(--slate-500);">
                            {{ _lang('Enter your credentials to continue') }}
                        </p>
                    </div>

                    @if (session('success'))
                        <div class="px-5 py-4 mb-6 border text-sm" style="border-radius: 4px; border-color: #a8d5b5; background: #eef6f0; color: #2f5c3c;">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="px-5 py-4 mb-6 border text-sm" style="border-radius: 4px; border-color: #e8b4b4; background: #fbecec; color: #7a2e2e;">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf

                        {{-- Email --}}
                        <div>
                            <label class="block mb-2 text-sm font-semibold" style="color: var(--ink-700);">
                                {{ _lang('Email / Phone') }}
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-4" style="color: var(--slate-500);">
                                    <i class="fa fa-user"></i>
                                </span>
                                <input type="text" name="email" value="{{ old('email') }}" required autofocus
                                    placeholder="{{ _lang('Enter Email or Phone') }}"
                                    class="w-full h-14 pr-4 border outline-none pl-11 transition"
                                    style="border-radius: 4px; border-color: var(--line-200); background: var(--paper-50);"
                                    onfocus="this.style.borderColor='var(--navy-800)'; this.style.background='#fff';"
                                    onblur="this.style.borderColor='var(--line-200)'; this.style.background='var(--paper-50)';">
                            </div>
                            @error('email') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        {{-- Password --}}
                        <div>
                            <label class="block mb-2 text-sm font-semibold" style="color: var(--ink-700);">
                                {{ _lang('Password') }}
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-4" style="color: var(--slate-500);">
                                    <i class="fa fa-lock"></i>
                                </span>
                                <input type="password" name="password" id="password-field" required
                                    placeholder="{{ _lang('Enter Password') }}"
                                    class="w-full h-14 pr-11 border outline-none pl-11 transition"
                                    style="border-radius: 4px; border-color: var(--line-200); background: var(--paper-50);"
                                    onfocus="this.style.borderColor='var(--navy-800)'; this.style.background='#fff';"
                                    onblur="this.style.borderColor='var(--line-200)'; this.style.background='var(--paper-50)';">
                                <button type="button" id="toggle-password" tabindex="-1"
                                    class="absolute inset-y-0 right-0 flex items-center pr-4" style="color: var(--slate-500);">
                                    <i class="fa fa-eye" id="toggle-password-icon"></i>
                                </button>
                            </div>
                            @error('password') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        {{-- Remember + Forgot --}}
                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-2.5 cursor-pointer select-none">
                                <input type="checkbox" name="remember" class="w-4 h-4">
                                <span class="text-sm" style="color: var(--ink-700);">{{ _lang('Remember Me') }}</span>
                            </label>

                            {{-- <a href="{{ route('password.request') }}" class="text-sm font-medium" style="color: var(--navy-800);">
                                {{ _lang('Forgot Password?') }}
                            </a> --}}
                        </div>

                        {{-- Submit --}}
                        <button type="submit" id="login-btn" class="btn-cta w-full justify-center !py-4 !text-base">
                            <i class="fa fa-sign-in"></i> {{ _lang('Login Now') }}
                        </button>

                        {{-- Demo Buttons (unchanged IDs/behavior) --}}
                        @if (is_demo_mode())
                            <div>
                                <p class="flex items-center gap-3 my-6 text-xs font-semibold uppercase tracking-widest" style="color: var(--slate-500);">
                                    <span class="flex-1 h-px" style="background: var(--line-200);"></span>
                                    {{ _lang('Quick Demo Login') }}
                                    <span class="flex-1 h-px" style="background: var(--line-200);"></span>
                                </p>

                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <button type="button" id="super-admin-login-btn"
                                        class="flex items-center gap-3 px-4 h-12 font-semibold border transition"
                                        style="border-radius: 4px; border-color: var(--line-200); background: var(--paper-50); color: var(--ink-700);">
                                        <i class="w-5 fa fa-user-shield" style="color: var(--navy-800);"></i> {{ _lang('Admin') }}
                                    </button>
                                    <button type="button" id="accountant-login-btn"
                                        class="flex items-center gap-3 px-4 h-12 font-semibold border transition"
                                        style="border-radius: 4px; border-color: var(--line-200); background: var(--paper-50); color: var(--ink-700);">
                                        <i class="w-5 fa fa-calculator" style="color: var(--navy-800);"></i> {{ _lang('Accountant') }}
                                    </button>
                                    <button type="button" id="librarian-login-btn"
                                        class="flex items-center gap-3 px-4 h-12 font-semibold border transition"
                                        style="border-radius: 4px; border-color: var(--line-200); background: var(--paper-50); color: var(--ink-700);">
                                        <i class="w-5 fa fa-book" style="color: var(--brass-600);"></i> {{ _lang('Librarian') }}
                                    </button>
                                    <button type="button" id="teacher-login-btn"
                                        class="flex items-center gap-3 px-4 h-12 font-semibold border transition"
                                        style="border-radius: 4px; border-color: var(--line-200); background: var(--paper-50); color: var(--ink-700);">
                                        <i class="w-5 fa fa-black-tie" style="color: var(--brass-600);"></i> {{ _lang('Teacher') }}
                                    </button>
                                    <button type="button" id="student-login-btn"
                                        class="flex items-center gap-3 px-4 h-12 font-semibold border transition sm:col-span-2"
                                        style="border-radius: 4px; border-color: var(--line-200); background: var(--paper-50); color: var(--ink-700);">
                                        <i class="w-5 fa fa-graduation-cap" style="color: var(--navy-800);"></i> {{ _lang('Student') }}
                                    </button>
                                </div>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </section>

    {{-- No @yield('scripts') exists in the shared layout, so this script
         goes directly in the content section — it still runs fine, since
         script execution doesn't depend on its position in the DOM. --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('toggle-password');
            const toggleIcon = document.getElementById('toggle-password-icon');
            const passwordField = document.getElementById('password-field');

            if (toggleBtn && passwordField) {
                toggleBtn.addEventListener('click', function() {
                    const isHidden = passwordField.type === 'password';
                    passwordField.type = isHidden ? 'text' : 'password';
                    toggleIcon.classList.toggle('fa-eye');
                    toggleIcon.classList.toggle('fa-eye-slash');
                });
            }

            const demoUsers = {
                'super-admin-login-btn': { email: 'superadmin@gmail.com', password: '12345678' },
                'accountant-login-btn': { email: 'accountant@gmail.com', password: '12345678' },
                'librarian-login-btn': { email: 'librarian@gmail.com', password: '12345678' },
                'teacher-login-btn': { email: 'teacher1@gmail.com', password: '12345678' },
                'student-login-btn': { email: 'student@gmail.com', password: '12345678' }
            };

            Object.keys(demoUsers).forEach(function(id) {
                const btn = document.getElementById(id);
                if (btn) {
                    btn.addEventListener('click', function() {
                        document.querySelector('input[name="email"]').value = demoUsers[id].email;
                        document.querySelector('input[name="password"]').value = demoUsers[id].password;
                        document.getElementById('login-btn').click();
                    });
                }
            });
        });
    </script>
@endsection