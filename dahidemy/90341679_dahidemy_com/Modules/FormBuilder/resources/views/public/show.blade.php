@extends('themes.' . config('frontend.active_theme', 'theme-three') . '.layouts.app')

@section('title', $form->name)

@section('content')
    <section class="py-20 bg-slate-50">
        <div class="max-w-3xl mx-auto px-4">
            <a href="{{ Route::has('public-form.index') ? route('public-form.index') : url('/') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-brand mb-8">
                <i class="fa-solid fa-arrow-left"></i> {{ Route::has('public-form.index') ? 'All Forms' : 'Back to Homepage' }}
            </a>

            <div class="card-elevated overflow-hidden">
                <div class="p-8 md:p-10 text-white" style="background: linear-gradient(135deg, var(--brand-600), var(--accent-500));">
                    @if ($form->icon)
                        <div class="w-14 h-14 rounded-2xl bg-white/15 flex items-center justify-center text-2xl mb-5">
                            <i class="{{ $form->icon }}"></i>
                        </div>
                    @endif
                    <h1 class="text-3xl md:text-4xl font-extrabold font-display">{{ $form->name }}</h1>
                    @if ($form->description)
                        <p class="mt-3 text-white/85 leading-7">{{ $form->description }}</p>
                    @endif
                </div>

                <div class="p-8 md:p-10">
                    @if (session('success'))
                        <div class="mb-6 px-5 py-4 rounded-2xl border border-emerald-200 bg-emerald-50 text-emerald-700 text-sm">
                            <i class="fa-solid fa-circle-check mr-2"></i>{{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-6 px-5 py-4 rounded-2xl border border-red-200 bg-red-50 text-red-700 text-sm">
                            <p class="font-semibold mb-1">Please fix the following:</p>
                            <ul class="list-disc pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('public-form.submit', $form->slug) }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @csrf

                        @foreach ($form->fields as $field)
                            <div class="{{ $field->width === 'half' ? 'md:col-span-1' : 'md:col-span-2' }}">
                                <label class="block mb-2 text-sm font-semibold text-slate-700">
                                    {{ $field->label }} @if ($field->is_required)<span class="text-pink-500">*</span>@endif
                                </label>

                                @switch($field->type)
                                    @case('textarea')
                                        <textarea name="{{ $field->field_key }}" rows="4" placeholder="{{ $field->placeholder }}"
                                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-brand focus:ring-4 focus:ring-brand-100 outline-none transition">{{ old($field->field_key) }}</textarea>
                                        @break

                                    @case('select')
                                        <select name="{{ $field->field_key }}"
                                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-brand focus:ring-4 focus:ring-brand-100 outline-none transition">
                                            <option value="">{{ $field->placeholder ?: 'Select an option' }}</option>
                                            @foreach ($field->options ?? [] as $option)
                                                <option value="{{ $option }}" @selected(old($field->field_key) == $option)>{{ $option }}</option>
                                            @endforeach
                                        </select>
                                        @break

                                    @case('radio')
                                        <div class="flex flex-wrap gap-4 pt-1">
                                            @foreach ($field->options ?? [] as $option)
                                                <label class="flex items-center gap-2 text-sm text-slate-700">
                                                    <input type="radio" name="{{ $field->field_key }}" value="{{ $option }}" @checked(old($field->field_key) == $option) class="text-brand focus:ring-brand">
                                                    {{ $option }}
                                                </label>
                                            @endforeach
                                        </div>
                                        @break

                                    @case('checkbox')
                                        <div class="flex flex-wrap gap-4 pt-1">
                                            @foreach ($field->options ?? [] as $option)
                                                <label class="flex items-center gap-2 text-sm text-slate-700">
                                                    <input type="checkbox" name="{{ $field->field_key }}[]" value="{{ $option }}"
                                                        @checked(is_array(old($field->field_key)) && in_array($option, old($field->field_key))) class="rounded text-brand focus:ring-brand">
                                                    {{ $option }}
                                                </label>
                                            @endforeach
                                        </div>
                                        @break

                                    @case('file')
                                        <input type="file" name="{{ $field->field_key }}"
                                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-brand-50 file:text-brand file:font-semibold">
                                        @break

                                    @default
                                        <input type="{{ $field->type === 'phone' ? 'tel' : $field->type }}" name="{{ $field->field_key }}"
                                            value="{{ old($field->field_key) }}" placeholder="{{ $field->placeholder }}"
                                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-brand focus:ring-4 focus:ring-brand-100 outline-none transition">
                                @endswitch

                                @error($field->field_key)
                                    <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach

                        <div class="md:col-span-2 pt-2">
                            <button type="submit" class="btn-cta w-full md:w-auto justify-center px-10 py-4">
                                {{ $form->submit_button_text ?: 'Submit' }} <i class="fa-solid fa-arrow-right"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
