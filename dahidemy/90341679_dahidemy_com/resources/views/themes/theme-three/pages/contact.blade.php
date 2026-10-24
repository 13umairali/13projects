@extends('themes.' . config('frontend.active_theme', 'theme-three') . '.layouts.app')

@section('title', 'Contact Us')

@section('content')
    <section class="py-24" style="background: var(--paper-100);">
        <div class="max-w-6xl mx-auto px-4">
            <div class="max-w-2xl mb-16">
                <span class="eyebrow mb-4">Get In Touch</span>
                <h1 class="section-title">Contact Us</h1>
            </div>

            <div class="grid lg:grid-cols-5 gap-12">
                <div class="lg:col-span-2 space-y-6">
                    @if (!empty($settings['phone']))
                        <div class="flex items-start gap-4">
                            <span class="w-11 h-11 flex items-center justify-center shrink-0" style="background: var(--brass-50); border-radius: var(--radius-sm);">
                                <i class="fa-solid fa-phone" style="color: var(--brass-600);"></i>
                            </span>
                            <div>
                                <p class="text-xs" style="color: var(--slate-500);">Phone</p>
                                <p class="font-semibold">{{ $settings['phone'] }}</p>
                            </div>
                        </div>
                    @endif
                    @if (!empty($settings['email']))
                        <div class="flex items-start gap-4">
                            <span class="w-11 h-11 flex items-center justify-center shrink-0" style="background: var(--brass-50); border-radius: var(--radius-sm);">
                                <i class="fa-solid fa-envelope" style="color: var(--brass-600);"></i>
                            </span>
                            <div>
                                <p class="text-xs" style="color: var(--slate-500);">Email</p>
                                <p class="font-semibold">{{ $settings['email'] }}</p>
                            </div>
                        </div>
                    @endif
                    @if (!empty($settings['address']))
                        <div class="flex items-start gap-4">
                            <span class="w-11 h-11 flex items-center justify-center shrink-0" style="background: var(--brass-50); border-radius: var(--radius-sm);">
                                <i class="fa-solid fa-location-dot" style="color: var(--brass-600);"></i>
                            </span>
                            <div>
                                <p class="text-xs" style="color: var(--slate-500);">Address</p>
                                <p class="font-semibold">{{ $settings['address'] }}</p>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="lg:col-span-3">
                    <div class="card-elevated p-8 md:p-10">
                        @if (session('success'))
                            <div class="mb-6 p-4 border" style="border-radius: var(--radius-sm); border-color: #7fae8a; background: #eef6f0; color: #2f5c3c;">
                                {{ session('success') }}
                            </div>
                        @endif

                        <form action="{{ route('contact.submit') }}" method="POST">
                            @csrf
                            <div class="grid sm:grid-cols-2 gap-5 mb-5">
                                <div>
                                    <label class="block text-sm font-semibold mb-2">Name</label>
                                    <input type="text" name="name" required value="{{ old('name') }}"
                                        class="w-full px-4 py-3 border" style="border-radius: var(--radius-sm); border-color: var(--line-200);">
                                    @error('name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold mb-2">Phone</label>
                                    <input type="text" name="phone" required value="{{ old('phone') }}"
                                        class="w-full px-4 py-3 border" style="border-radius: var(--radius-sm); border-color: var(--line-200);">
                                    @error('phone') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <div class="mb-5">
                                <label class="block text-sm font-semibold mb-2">Email</label>
                                <input type="email" name="email" required value="{{ old('email') }}"
                                    class="w-full px-4 py-3 border" style="border-radius: var(--radius-sm); border-color: var(--line-200);">
                                @error('email') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="mb-6">
                                <label class="block text-sm font-semibold mb-2">Message</label>
                                <textarea name="message" rows="5" required
                                    class="w-full px-4 py-3 border" style="border-radius: var(--radius-sm); border-color: var(--line-200);">{{ old('message') }}</textarea>
                                @error('message') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <button type="submit" class="btn-cta">Send Message</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
