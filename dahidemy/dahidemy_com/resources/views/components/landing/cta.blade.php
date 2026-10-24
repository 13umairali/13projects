@props(['brand' => []])

<section class="py-20 sm:py-24">
    <div class="container-page">
        <div class="reveal relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-600 to-accent-600 px-8 py-16 text-center shadow-[var(--shadow-glow)] sm:px-16">
            <div class="pointer-events-none absolute inset-0 opacity-30"
                style="background-image: radial-gradient(circle at 20% 20%, rgba(255,255,255,.25), transparent 40%), radial-gradient(circle at 80% 80%, rgba(255,255,255,.2), transparent 45%);"></div>

            <h2 class="relative text-3xl font-bold tracking-tight text-white sm:text-4xl">
                Ready to modernize {{ $brand['name'] ?? 'your institution' }}?
            </h2>
            <p class="relative mx-auto mt-4 max-w-xl text-lg text-white/85">
                Join thousands of schools already running smarter. Set up in minutes — no credit card required.
            </p>
            <div class="relative mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                <a href="{{ route('register.show') }}" class="btn w-full bg-white text-brand-700 hover:bg-brand-50 sm:w-auto">
                    Start free trial
                </a>
                <a href="{{ route('contact') }}" class="btn w-full border border-white/40 text-white hover:bg-white/10 sm:w-auto">
                    Talk to sales
                </a>
            </div>
        </div>
    </div>
</section>
