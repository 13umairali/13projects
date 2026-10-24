<section class="border-t border-slate-200 bg-slate-50/60 py-16 dark:border-white/10 dark:bg-white/[0.02]">
    <div class="container-page">
        <div class="reveal mx-auto flex max-w-3xl flex-col items-center gap-6 text-center lg:flex-row lg:justify-between lg:text-left">
            <div>
                <h3 class="text-xl font-semibold text-slate-900 dark:text-white">Stay in the loop</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Product updates, education insights and tips — once a month, no spam.</p>
            </div>
            <form action="{{ route('newsletter.subscribe') }}" method="POST"
                x-data="{ sent: {{ session('newsletter_ok') ? 'true' : 'false' }} }"
                class="flex w-full max-w-md flex-col gap-2 sm:flex-row">
                @csrf
                <label for="newsletter-email" class="sr-only">Email address</label>
                <input id="newsletter-email" type="email" name="email" required placeholder="you@school.com"
                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30 dark:border-white/10 dark:bg-slate-900 dark:text-white">
                <button type="submit" class="btn-primary whitespace-nowrap">Subscribe</button>
            </form>
        </div>
        @if (session('newsletter_ok'))
            <p class="mt-3 text-center text-sm font-medium text-emerald-600 dark:text-emerald-400">Thanks! You’re subscribed. 🎉</p>
        @endif
    </div>
</section>
