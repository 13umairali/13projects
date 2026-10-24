{{-- =========================================================
    Admin-managed "Ready To Join Us" CTA blocks. Rebuilt as
    solid navy panels with a brass rule accent, replacing the
    purple-pink gradient cards with a floating decorative circle.
========================================================= --}}
@if ($data['readyToJoinUs'] && count($data['readyToJoinUs']) > 0)
    <section class="py-6">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid md:grid-cols-{{ min($data['readyToJoinUs']->count(), 3) }} gap-6">
                @foreach ($data['readyToJoinUs'] as $item)
                    <div class="relative p-9 text-white" style="background: var(--surface-dark); border-radius: var(--radius-md);" data-aos="fade-up">
                        <div class="w-1 h-8 mb-6" style="background: var(--brass-500);"></div>

                        @if (!empty($item->icon))
                            <img src="{{ asset('storage/ready_to_join_us/' . $item->icon) }}" class="w-12 h-12 object-contain mb-5" alt="">
                        @else
                            <i class="fa-solid fa-graduation-cap text-2xl mb-5" style="color: var(--brass-500);"></i>
                        @endif

                        <h3 class="text-2xl font-display mb-3">{{ $item->title }}</h3>
                        <p class="text-white/70 leading-7 mb-6">{{ $item->description }}</p>

                        @if ($item->button_name && $item->button_link)
                            <a href="{{ $item->button_link }}" class="btn-cta">
                                {{ $item->button_name }}
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif