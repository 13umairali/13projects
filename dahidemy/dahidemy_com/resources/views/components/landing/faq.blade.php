@props(['faqs' => collect()])

<section id="faq" class="py-20 sm:py-28">
    <div class="container-page">
        <x-landing.section-heading
            eyebrow="FAQ"
            title='Questions? <span class="gradient-text">Answered</span>'
            subtitle="Everything you need to know before getting started." />

        <div class="mx-auto mt-12 max-w-3xl divide-y divide-slate-200 dark:divide-white/10" x-data="{ open: 0 }">
            @forelse ($faqs as $i => $faq)
                @php $faq = (array) $faq; @endphp
                <div class="reveal">
                    <button type="button" @click="open === {{ $i }} ? open = null : open = {{ $i }}"
                        class="flex w-full items-center justify-between gap-4 py-5 text-left"
                        :aria-expanded="open === {{ $i }}">
                        <span class="font-semibold text-slate-900 dark:text-white">{{ $faq['question'] ?? $faq['title'] ?? '' }}</span>
                        <svg class="h-5 w-5 flex-none text-brand-500 transition-transform duration-300" :class="open === {{ $i }} && 'rotate-45'"
                            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                    </button>
                    <div x-show="open === {{ $i }}" x-collapse x-cloak>
                        <p class="pb-5 pr-8 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                            {{ $faq['answer'] ?? $faq['description'] ?? '' }}
                        </p>
                    </div>
                </div>
            @empty
                <x-landing.empty title="No FAQs published yet"
                    message="Questions added from the admin panel will appear here."
                    icon="M12 22a10 10 0 100-20 10 10 0 000 20zM9.1 9a3 3 0 015.8 1c0 2-3 3-3 3M12 17h.01" />
            @endforelse
        </div>
    </div>
</section>
