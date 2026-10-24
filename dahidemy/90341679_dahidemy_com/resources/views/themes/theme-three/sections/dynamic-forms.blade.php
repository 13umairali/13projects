{{-- =========================================================
    NEW SECTION — Dynamic Form Builder integration. Shows any
    published form the institute admin has flagged
    "show_on_homepage" (Modules/FormBuilder) as a clickable card
    linking to its public /forms/{slug} page. Renders nothing if
    the module isn't installed yet or no form is featured.
========================================================= --}}
@if (isset($data['dynamicForms']) && $data['dynamicForms']->count() > 0)
    <section class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-14" data-aos="fade-up">
                <span class="eyebrow mb-4">Get Started</span>
                <h2 class="section-title">Apply Online</h2>
                <p class="text-slate-500 mt-4 max-w-xl mx-auto">Fill out the right form and we'll take it from there.</p>
            </div>

            <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-8">
                @foreach ($data['dynamicForms'] as $dynamicForm)
                    <a href="{{ route('public-form.show', $dynamicForm->slug) }}"
                        class="card-elevated tilt-3d p-8 group" data-aos="fade-up" data-aos-delay="{{ $loop->iteration * 100 }}">
                        <div class="w-16 h-16 rounded-2xl bg-brand-50 text-brand flex items-center justify-center text-2xl mb-6 group-hover:bg-brand-600 group-hover:text-white transition-colors duration-300">
                            <i class="{{ $dynamicForm->icon ?: 'fa-solid fa-file-lines' }}"></i>
                        </div>
                        <h3 class="text-xl font-bold mb-2 font-display">{{ $dynamicForm->name }}</h3>
                        <p class="text-slate-500 text-sm leading-6 mb-6">
                            {{ Str::limit($dynamicForm->description ?: 'Complete this form to get started.', 100) }}
                        </p>
                        <span class="inline-flex items-center gap-2 text-brand font-semibold text-sm">
                            {{ $dynamicForm->submit_button_text ?: 'Apply Now' }} <i class="fa-solid fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
