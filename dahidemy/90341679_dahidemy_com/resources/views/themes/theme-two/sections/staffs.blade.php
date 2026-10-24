@if ($data['staffs'] && count($data['staffs']) > 0)
    <section class="py-24 bg-white border-y" style="border-color:var(--nl-line)">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center max-w-2xl mx-auto mb-16" data-aos="fade-up">
                <span class="eyebrow justify-center">Our Team</span>
                <h2 class="font-display text-4xl mt-4">Meet the staff</h2>
            </div>

            <div class="grid md:grid-cols-2 xl:grid-cols-4 gap-6">
                @foreach ($data['staffs'] as $staff)
                    <div class="nl-card overflow-hidden" data-aos="fade-up" data-aos-delay="{{ $loop->iteration * 80 }}">
                        <img src="{{ $staff->user?->image && file_exists(public_path('storage/users/' . $staff->user?->image))
                            ? asset('storage/users/' . $staff->user?->image)
                            : asset('images/user.png') }}"
                            class="w-full h-64 object-cover grayscale hover:grayscale-0 transition duration-500">
                        <div class="p-6">
                            <h3 class="font-display text-lg mb-1">{{ $staff->user?->name }}</h3>
                            <p class="text-brand text-sm font-semibold uppercase tracking-wide">
                                {{ $staff->designation ?? 'Staff' }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
