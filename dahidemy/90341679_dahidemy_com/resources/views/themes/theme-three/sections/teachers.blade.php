@if ($data['teachers'] && count($data['teachers']) > 0)
    <section id="teachers" class="relative py-28 bg-slate-50 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16" data-aos="fade-up">
                <span class="eyebrow mb-4">Our Faculty</span>
                <h2 class="section-title">Meet Our Professional Teachers</h2>
                <p class="text-slate-500 mt-4 max-w-2xl mx-auto leading-8">
                    Dedicated educators helping students achieve excellence and success.
                </p>
            </div>

            <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-8">
                @foreach ($data['teachers'] as $teacher)
                    <div class="group relative" data-aos="fade-up" data-aos-delay="{{ $loop->iteration * 100 }}">
                        <div class="card-elevated overflow-hidden text-center">
                            <div class="relative overflow-hidden">
                                <img src="{{ $teacher->user?->image && file_exists(public_path('storage/users/' . $teacher->user?->image))
                                    ? asset('storage/users/' . $teacher->user?->image)
                                    : asset('images/user.png') }}"
                                    class="w-full h-72 object-cover group-hover:scale-110 transition duration-700" alt="{{ $teacher->user?->name }}">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                                <div class="absolute bottom-4 left-0 right-0 flex justify-center gap-3 opacity-0 group-hover:opacity-100 transition duration-500">
                                    <a href="#" class="w-10 h-10 rounded-full bg-white text-slate-900 flex items-center justify-center hover:bg-brand hover:text-white transition"><i class="fab fa-facebook-f"></i></a>
                                    <a href="#" class="w-10 h-10 rounded-full bg-white text-slate-900 flex items-center justify-center hover:bg-brand hover:text-white transition"><i class="fab fa-linkedin-in"></i></a>
                                </div>
                            </div>
                            <div class="p-6">
                                <h3 class="text-lg font-bold mb-1 font-display">{{ $teacher->user?->name }}</h3>
                                <p class="text-brand font-semibold text-sm">{{ $teacher->designation ?? 'Teacher' }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
