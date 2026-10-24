<section class="bg-ink text-white py-16">
    <div class="max-w-7xl mx-auto px-4 grid grid-cols-2 md:grid-cols-4 gap-10 text-center">

        <div data-aos="fade-up">
            <h3 class="font-display text-5xl mb-2">{{ $data['teachers']->count() }}+</h3>
            <p class="text-white/60 uppercase text-sm tracking-wide">Expert Teachers</p>
        </div>

        <div data-aos="fade-up" data-aos-delay="100">
            <h3 class="font-display text-5xl mb-2">{{ $data['courses']->count() }}+</h3>
            <p class="text-white/60 uppercase text-sm tracking-wide">Courses</p>
        </div>

        <div data-aos="fade-up" data-aos-delay="200">
            <h3 class="font-display text-5xl mb-2">{{ $data['events']->count() }}+</h3>
            <p class="text-white/60 uppercase text-sm tracking-wide">Events</p>
        </div>

        <div data-aos="fade-up" data-aos-delay="300">
            <h3 class="font-display text-5xl mb-2">{{ $data['testimonials']->count() }}+</h3>
            <p class="text-white/60 uppercase text-sm tracking-wide">Happy Reviews</p>
        </div>

    </div>
</section>
