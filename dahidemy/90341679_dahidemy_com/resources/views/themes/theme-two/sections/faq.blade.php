@if ($data['faqs'] && count($data['faqs']) > 0)
    <section class="py-24">
        <div class="max-w-3xl mx-auto px-4">
            <div class="text-center mb-14" data-aos="fade-up">
                <span class="eyebrow justify-center">FAQ</span>
                <h2 class="font-display text-4xl mt-4">Frequently asked questions</h2>
            </div>

            <div class="space-y-3">
                @foreach ($data['faqs'] as $faq)
                    <details class="nl-faq nl-card p-6">
                        <summary class="flex items-center justify-between font-semibold">
                            {{ $faq->question }}
                            <span class="nl-faq-icon text-brand text-xl leading-none">+</span>
                        </summary>
                        <p class="leading-7 mt-4" style="color:var(--nl-muted)">
                            {{ $faq->answer }}
                        </p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>
@endif
