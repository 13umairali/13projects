<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
<script src="//unpkg.com/alpinejs" defer></script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        AOS.init({ duration: 900, once: true, offset: 60 });

        /* ---------- ANIMATED COUNTERS ---------- */
        const counters = document.querySelectorAll('[data-counter]');
        const runCounter = (el) => {
            const target = parseInt(el.dataset.counter, 10) || 0;
            const duration = 1400;
            const start = performance.now();
            const step = (now) => {
                const progress = Math.min((now - start) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                el.textContent = Math.floor(eased * target).toLocaleString();
                if (progress < 1) requestAnimationFrame(step);
                else el.textContent = target.toLocaleString();
            };
            requestAnimationFrame(step);
        };

        if ('IntersectionObserver' in window && counters.length) {
            const obs = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        runCounter(entry.target);
                        obs.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.4 });
            counters.forEach((c) => obs.observe(c));
        }

        /* ---------- FAQ: only one open at a time ---------- */
        document.querySelectorAll('.faq-item').forEach((item) => {
            item.addEventListener('toggle', () => {
                if (item.open) {
                    document.querySelectorAll('.faq-item').forEach((other) => {
                        if (other !== item) other.open = false;
                    });
                }
            });
        });

        /* ---------- BACK TO TOP ---------- */
        const backToTop = document.getElementById('backToTop');
        if (backToTop) {
            window.addEventListener('scroll', () => {
                if (window.scrollY > 500) {
                    backToTop.classList.remove('opacity-0', 'pointer-events-none');
                } else {
                    backToTop.classList.add('opacity-0', 'pointer-events-none');
                }
            });
            backToTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
        }

        /* ---------- SUBTLE 3D TILT ON CARDS ---------- */
        document.querySelectorAll('.tilt-3d').forEach((el) => {
            el.addEventListener('mousemove', (e) => {
                const rect = el.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                const rotateX = ((y / rect.height) - 0.5) * -8;
                const rotateY = ((x / rect.width) - 0.5) * 8;
                el.style.transform = `perspective(900px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-6px)`;
            });
            el.addEventListener('mouseleave', () => { el.style.transform = ''; });
        });
    });
</script>
