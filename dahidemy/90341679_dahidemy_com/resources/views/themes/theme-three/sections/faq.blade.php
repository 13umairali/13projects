@if ($data['faqs'] && count($data['faqs']) > 0)
    {{-- Deliberately a LIGHT section: the previous version stacked the same dark
         mesh background across achievements -> testimonials -> FAQ -> footer, which
         reads as one heavy, monotonous block by the time visitors reach the
         bottom of the page. This breather restores contrast and rhythm. --}}
    <section id="faq" class="relative py-28 bg-white overflow-hidden">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[700px] h-[300px] bg-brand-50 blur-[100px] rounded-full pointer-events-none"></div>

        <div class="max-w-4xl mx-auto px-4 relative z-10">
            <div class="text-center mb-14" data-aos="fade-up">
                <span class="eyebrow mb-5">Got Questions?</span>
                <h2 class="section-title">Frequently Asked Questions</h2>
                <p class="text-slate-500 mt-4">Everything you need to know before you apply.</p>
            </div>

            <div class="space-y-4">
                @foreach ($data['faqs'] as $faq)
                    <details class="faq-item bg-slate-50 border border-slate-100 rounded-2xl px-6 py-5 open:bg-white open:shadow-lg open:border-brand-100 transition-all" data-aos="fade-up" data-aos-delay="{{ $loop->iteration * 60 }}">
                        <summary class="flex justify-between items-center gap-4">
                            <span class="text-base md:text-lg font-semibold text-slate-800">{{ $faq->question }}</span>
                            <span class="faq-icon w-8 h-8 rounded-full bg-brand-50 text-brand flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-plus text-sm"></i>
                            </span>
                        </summary>
                        <div class="pt-4 text-slate-500 leading-7 text-sm md:text-base">
                            {{ $faq->answer }}
                        </div>
                    </details>
                @endforeach
            </div>
        </div>
    </section>
@endif
