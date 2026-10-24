@extends('layouts.landing')

@section('content')
    <section class="relative overflow-hidden pt-32 pb-24 sm:pt-40">
        <div class="hero-aurora absolute inset-0 -z-10"></div>
        <div class="container-page grid gap-10 lg:grid-cols-[1.4fr_1fr]">
            {{-- Form --}}
            <div class="reveal">
                <span class="eyebrow">Get started</span>
                <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl dark:text-white">
                    Register your institution
                </h1>
                <p class="mt-3 text-slate-600 dark:text-slate-400">
                    Create your workspace in minutes. Once submitted, our team reviews and activates your account.
                </p>

                @if (session('success'))
                    <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="mt-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300">
                        {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('register.store') }}" method="POST" enctype="multipart/form-data" class="mt-8 space-y-8">
                    @csrf

                    {{-- Plan --}}
                    <div class="space-y-4">
                        <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-400">Choose your plan</h2>
                        <div class="grid gap-4 sm:grid-cols-3">
                            @foreach ($packages as $package)
                                <label class="relative block cursor-pointer">
                                    <input type="radio" name="plan_id" value="{{ $package['id'] }}" class="peer sr-only"
                                        @checked(old('plan_id', $selected['id'] ?? ($package['is_free'] ? $package['id'] : null)) == $package['id'])
                                        required>

                                    {{-- Checkmark badge - only visible when this specific card is
                                         selected, so which plan is chosen is unambiguous even before
                                         looking at the border color. --}}
                                    <span class="absolute -top-2 -right-2 z-10 hidden h-6 w-6 items-center justify-center rounded-full bg-brand-600 text-white shadow-lg peer-checked:flex">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </span>

                                    <div class="h-full rounded-xl border-2 border-slate-200 bg-white p-5 transition peer-checked:border-brand-600 peer-checked:bg-brand-50/60 peer-checked:shadow-md dark:border-white/10 dark:bg-transparent dark:peer-checked:bg-brand-500/10">
                                        @if ($package['is_free'])
                                            <span class="inline-block mb-2 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">
                                                FREE TRIAL
                                            </span>
                                        @endif

                                        <p class="font-bold text-slate-900 dark:text-white">{{ $package['name'] }}</p>

                                        <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">
                                            @if ($package['is_custom'])
                                                Custom
                                            @elseif ($package['is_free'])
                                                $0
                                            @else
                                                ${{ rtrim(rtrim(number_format($package['price'], 2), '0'), '.') }}
                                                <span class="text-sm font-normal text-slate-400">/{{ $package['cycle'] }}</span>
                                            @endif
                                        </p>

                                        @if (! empty($package['student_limit']))
                                            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                                                Up to {{ number_format($package['student_limit']) }} students
                                            </p>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        @error('plan_id')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                    </div>

                    {{-- Institute --}}
                    <div class="space-y-4">
                        <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-400">Institution details</h2>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-landing.field name="institute_name" label="Institution name" :value="old('institute_name')" required />
                            <x-landing.field name="institute_email" type="email" label="Institution email" :value="old('institute_email')" required />
                            <x-landing.field name="institute_phone" label="Institution phone" :value="old('institute_phone')" required />

                            <div>
                                <label for="institute_type" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Institution type</label>
                                <select id="institute_type" name="institute_type"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30 dark:border-white/10 dark:bg-slate-900 dark:text-white">
                                    <option value="">— Select type —</option>
                                    @foreach ($instituteTypes as $type)
                                        @php $type = (array) $type; $name = $type['name'] ?? ($type['title'] ?? null); @endphp
                                        @if ($name)
                                            <option value="{{ $name }}" @selected(old('institute_type') === $name)>{{ $name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                @error('institute_type')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                            </div>

                            {{-- Subdomain & Logo Row --}}
                            <div>
                                <label for="institute_domain" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Choose your subdomain
                                </label>
                                <div class="flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 transition focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/30 dark:border-white/10 dark:bg-slate-900 dark:text-white">
                                    <svg class="h-4 w-4 flex-none text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5l6-6M9 15l-1.5 1.5a3.182 3.182 0 01-4.5-4.5L6 9m9-4.5l1.5-1.5a3.182 3.182 0 014.5 4.5L19.5 9m-11 6l6-6"/>
                                    </svg>
                                    <input
                                        id="institute_domain"
                                        name="institute_domain"
                                        type="text"
                                        value="{{ old('institute_domain') }}"
                                        placeholder="institute"
                                        maxlength="63"
                                        pattern="[a-z0-9]+(-[a-z0-9]+)*"
                                        autocomplete="off"
                                        autocapitalize="off"
                                        spellcheck="false"
                                        class="w-full flex-1 border-0 bg-transparent p-0 text-sm text-slate-900 outline-none focus:ring-0 dark:text-white"
                                    >
                                    <span class="flex-none font-semibold text-slate-500 dark:text-slate-400">.dahidemy.com</span>
                                </div>
                                <p class="mt-1.5 text-xs text-slate-400">Lowercase letters, numbers and hyphens only.</p>
                                @error('institute_domain')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                            </div>

                            {{-- Referral Code — pre-filled automatically if this page was
                                 reached via someone's shareable referral link
                                 (?ref=CODE), but always manually editable too. --}}
                            <x-landing.field name="referral_code" label="Referral code (optional)"
                                :value="old('referral_code', request()->query('ref'))"
                                placeholder="Have a code? Enter it here" />
                            <p class="-mt-3 text-xs text-slate-400">
                                Referred by someone? Enter their code and they may earn a reward once your institution is approved.
                            </p>

                            <div>
                                <label for="institute_logo" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Institution logo <span class="text-slate-400">(optional)</span></label>
                                <input id="institute_logo" name="institute_logo" type="file" accept="image/jpeg,image/png,image/jpg,image/gif"
                                    class="block w-full text-sm text-slate-500 file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-brand-600 hover:file:bg-brand-100 dark:text-slate-400 dark:file:bg-brand-500/10 dark:file:text-brand-300">
                                @error('institute_logo')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                            </div>

                            {{-- Address Field --}}
                            <div class="sm:col-span-2">
                                <label for="institute_address" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Address</label>
                                <input id="institute_address" name="institute_address" type="text" value="{{ old('institute_address') }}"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30 dark:border-white/10 dark:bg-slate-900 dark:text-white">
                                @error('institute_address')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>

                    {{-- Owner --}}
                    <div class="space-y-4">
                        <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-400">Your admin account</h2>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-landing.field name="user_name" label="Your name" :value="old('user_name')" required />
                            <x-landing.field name="user_email" type="email" label="Your email" :value="old('user_email')" required />
                            <x-landing.field name="user_phone" label="Your phone" :value="old('user_phone')" required />
                            <div>
                                <label for="user_avatar" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Your photo <span class="text-slate-400">(optional)</span></label>
                                <input id="user_avatar" name="user_avatar" type="file" accept="image/jpeg,image/png,image/jpg,image/gif"
                                    class="block w-full text-sm text-slate-500 file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-brand-600 hover:file:bg-brand-100 dark:text-slate-400 dark:file:bg-brand-500/10 dark:file:text-brand-300">
                                @error('user_avatar')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                            </div>
                            <x-landing.field name="password" type="password" label="Password" required />
                            <x-landing.field name="password_confirmation" type="password" label="Confirm password" required />
                        </div>
                    </div>

                    <label class="flex items-start gap-2.5 text-sm text-slate-600 dark:text-slate-400">
                        <input type="checkbox" name="terms" value="1" @checked(old('terms')) required class="mt-0.5 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                        <span>I agree to the <a href="{{ route('policy', 'terms') }}" class="font-semibold text-brand-600">Terms</a>
                        and <a href="{{ route('policy', 'privacy') }}" class="font-semibold text-brand-600">Privacy Policy</a>.</span>
                    </label>
                    @error('terms')<p class="-mt-4 text-xs text-rose-500">{{ $message }}</p>@enderror

                    <button type="submit" class="btn-primary w-full">Create my institution</button>
                </form>
            </div>

            {{-- Summary --}}
            <aside class="reveal">
                <div class="card sticky top-24">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">What happens next</h2>
                    @if ($selected)
                        <div class="mt-4 flex items-baseline justify-between">
                            <span class="text-xl font-bold text-slate-900 dark:text-white">{{ $selected['name'] }}</span>
                            <span class="text-lg font-bold text-brand-600 dark:text-brand-400">
                                @if ($selected['is_custom'] ?? false) Custom @else ${{ rtrim(rtrim(number_format((float) $selected['price'], 2), '0'), '.') }}/{{ $selected['cycle'] ?? 'mo' }} @endif
                            </span>
                        </div>
                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ $selected['description'] ?? '' }}</p>
                    @endif
                    <ul class="mt-5 space-y-3 text-sm">
                        @foreach (['We create your institution workspace', 'Your admin login is emailed to you', 'Start on a free trial — upgrade anytime'] as $step)
                            <li class="flex items-start gap-2.5 text-slate-600 dark:text-slate-300">
                                <svg class="mt-0.5 h-4 w-4 flex-none text-brand-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                                {{ $step }}
                            </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('pricing') }}" class="mt-6 inline-flex items-center gap-1.5 text-sm font-semibold text-brand-600 dark:text-brand-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                        Compare plans
                    </a>
                </div>
            </aside>
        </div>
    </section>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var domainInput = document.getElementById('institute_domain');
        var nameInput = document.getElementById('institute_name');
        if (!domainInput) return;

        function slugify(value) {
            return value
                .toLowerCase()
                .normalize('NFKD').replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '')
                .slice(0, 63);
        }

        var domainEdited = domainInput.value.length > 0;

        domainInput.addEventListener('input', function () {
            domainInput.value = slugify(domainInput.value);
            domainEdited = domainInput.value.length > 0;
        });

        if (nameInput) {
            nameInput.addEventListener('input', function () {
                if (!domainEdited) {
                    domainInput.value = slugify(nameInput.value);
                }
            });
        }
    });
    </script>
@endsection