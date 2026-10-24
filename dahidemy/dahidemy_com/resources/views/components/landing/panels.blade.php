@props(['panels' => collect()])

{{-- s_a_a_s_panels: title, description, icon (uploaded image or built-in name),
     button_name, button_link. Previously this table had admin CRUD but no
     section on the site, so nothing an admin entered here was ever visible. --}}
@if ($panels->isNotEmpty())
    <section id="panels" class="py-20 sm:py-28">
        <div class="container-page">
            <x-landing.section-heading eyebrow="One platform, every role"
                title='A panel for <span class="gradient-text">everyone</span>'
                subtitle="Administrators, teachers, parents and students each get a workspace built for what they actually do." />

            <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($panels as $panel)
                    @php $panel = (array) $panel; @endphp
                    <article class="reveal card flex flex-col text-center">
                        <span
                            class="mx-auto grid h-14 w-14 place-items-center overflow-hidden rounded-2xl bg-gradient-to-br from-brand-500 to-accent-500 text-white shadow-lg shadow-brand-600/25">
                            <x-landing.icon :name="$panel['icon'] ?? 'sparkles'" storage="saas_panels"
                                class="h-7 w-7" />
                        </span>

                        <h3 class="mt-5 text-lg font-semibold text-slate-900 dark:text-white">
                            {{ $panel['title'] ?? '' }}
                        </h3>

                        <p class="mt-2 grow text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                            {{ $panel['description'] ?? '' }}
                        </p>

                        @if (filled($panel['button_name'] ?? null))
                            <a href="{{ $panel['button_link'] ?: route('features') }}"
                                class="mt-5 inline-flex items-center justify-center gap-1.5 text-sm font-semibold text-brand-600 transition hover:gap-2.5 dark:text-brand-400">
                                {{ $panel['button_name'] }}
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path d="M5 12h14M13 5l7 7-7 7" />
                                </svg>
                            </a>
                        @endif
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif
