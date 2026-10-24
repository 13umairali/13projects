@if ($data['staffs'] && count($data['staffs']) > 0)
    <section id="staffs" class="relative py-28 bg-white overflow-hidden">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16" data-aos="fade-up">
                <span class="eyebrow mb-4">Behind The Scenes</span>
                <h2 class="section-title">Meet Our Support Staff</h2>
            </div>

            <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-8">
                @foreach ($data['staffs'] as $staff)
                    <div class="group relative" data-aos="fade-up" data-aos-delay="{{ $loop->iteration * 100 }}">
                        <div class="card-elevated overflow-hidden text-center">
                            <div class="relative overflow-hidden">
                                <img src="{{ $staff->user?->image && file_exists(public_path('storage/users/' . $staff->user?->image))
                                    ? asset('storage/users/' . $staff->user?->image)
                                    : asset('images/user.png') }}"
                                    class="w-full h-72 object-cover group-hover:scale-110 transition duration-700" alt="{{ $staff->user?->name }}">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                            </div>
                            <div class="p-6">
                                <h3 class="text-lg font-bold mb-1 font-display">{{ $staff->user?->name }}</h3>
                                <p class="text-brand font-semibold text-sm">{{ $staff->designation ?? 'Staff' }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
