@if (isset($data['directorMessage']) && $data['directorMessage'] && $data['directorMessage']->hasContent())
    <section class="py-24 bg-slate-50">
        <div class="max-w-5xl mx-auto px-4">
            <div class="card-elevated p-10 md:p-14 grid md:grid-cols-3 gap-10 items-center" data-aos="fade-up">
                <div class="md:col-span-1 text-center">
                    @if ($data['directorMessage']->photo)
                        <img src="{{ asset('storage/director_messages/' . $data['directorMessage']->photo) }}"
                            class="w-40 h-40 rounded-full object-cover mx-auto shadow-lg" alt="{{ $data['directorMessage']->director_name }}">
                    @else
                        <div class="w-40 h-40 rounded-full mx-auto flex items-center justify-center text-white text-4xl"
                            style="background: linear-gradient(135deg, var(--brand-600), var(--accent-500));">
                            <i class="fa-solid fa-user-tie"></i>
                        </div>
                    @endif
                    <h3 class="mt-5 font-bold text-lg font-display">{{ $data['directorMessage']->director_name }}</h3>
                    @if ($data['directorMessage']->designation)
                        <p class="text-brand text-sm font-semibold">{{ $data['directorMessage']->designation }}</p>
                    @endif
                </div>

                <div class="md:col-span-2">
                    <span class="eyebrow mb-4">A Message From Our Leadership</span>
                    <div class="text-4xl text-brand-200 font-display mb-3">&#10078;</div>
                    <p class="text-slate-600 leading-8 text-lg italic">{{ $data['directorMessage']->message }}</p>
                </div>
            </div>
        </div>
    </section>
@endif
