import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

Alpine.plugin(collapse);

/**
 * Global theme store — persists light/dark to localStorage and reflects it on
 * <html data-theme>. The initial value is set by a blocking inline script in
 * the layout head to avoid a flash of the wrong theme.
 */
Alpine.store('theme', {
    current: document.documentElement.getAttribute('data-theme') || 'light',

    toggle() {
        this.current = this.current === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', this.current);
        try {
            localStorage.setItem('theme', this.current);
        } catch (e) {
            /* storage unavailable — ignore */
        }
    },

    get isDark() {
        return this.current === 'dark';
    },
});

window.Alpine = Alpine;
Alpine.start();

/**
 * Lightweight scroll-reveal: adds .is-visible to .reveal elements as they
 * enter the viewport. No dependency, respects reduced-motion via CSS.
 */
const revealObserver = new IntersectionObserver(
    (entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                revealObserver.unobserve(entry.target);
            }
        });
    },
    { threshold: 0.12, rootMargin: '0px 0px -40px 0px' }
);

const initReveal = () => {
    document.querySelectorAll('.reveal:not(.is-visible)').forEach((el) => revealObserver.observe(el));
};

document.addEventListener('DOMContentLoaded', initReveal);
