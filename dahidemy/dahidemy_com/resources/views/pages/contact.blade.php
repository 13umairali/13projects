@extends('layouts.landing')

@section('content')
    <x-landing.page-hero
        eyebrow="Contact"
        title='Let’s talk about <span class="gradient-text">your institution</span>'
        subtitle="Questions, demos or custom plans — send us a note and we’ll get back within one business day." />

    <section class="pb-24">
        <div class="container-page grid gap-10 lg:grid-cols-[1fr_1.2fr]">
            {{-- Contact info --}}
            <div class="reveal space-y-6">
                    @foreach ([
                        ['label' => 'Email', 'value' => $brand['email'] ?? config('saas.brand.email'), 'href' => 'mailto:'.($brand['email'] ?? config('saas.brand.email')), 'icon' => 'M4 4h16v16H4z|M22 6l-10 7L2 6'],
                    ] as $item)
                    <div class="flex items-start gap-4">
                        <div class="grid h-11 w-11 flex-none place-items-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                @foreach (explode('|', $item['icon']) as $p)<path d="{{ $p }}"/>@endforeach
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ $item['label'] }}</p>
                            @if ($item['href'])
                                <a href="{{ $item['href'] }}" class="font-semibold text-slate-900 hover:text-brand-600 dark:text-white">{{ $item['value'] }}</a>
                            @else
                                <p class="font-semibold text-slate-900 dark:text-white">{{ $item['value'] }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Form --}}
            <div class="reveal card">
                @if (session('success'))
                    <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300">
                        {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('contact.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-landing.field name="name" label="Full name" :value="old('name')" required />
                        <x-landing.field name="email" type="email" label="Email" :value="old('email')" required />
                    </div>
                    <x-landing.field name="phone" label="Phone" :value="old('phone')" required />
                    <x-landing.field name="subject" label="Subject" :value="old('subject')" required />
                    <div>
                        <label for="message" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Message</label>
                        <textarea id="message" name="message" rows="4" required
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30 dark:border-white/10 dark:bg-slate-900 dark:text-white">{{ old('message') }}</textarea>
                        @error('message')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="btn-primary w-full">Send message</button>
                </form>
            </div>
        </div>
    </section>
@endsection
