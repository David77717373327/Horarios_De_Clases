/**
 * welcome.js — Animaciones y efectos interactivos
 */
document.addEventListener('DOMContentLoaded', () => {

    // 1. SCROLL SUAVE
    document.querySelectorAll('a[href^="#"]').forEach(link => {
        link.addEventListener('click', e => {
            const target = document.querySelector(link.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // 2. APARICIÓN ESCALONADA de tarjetas
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            const el    = entry.target;
            const idx   = parseInt(el.dataset.step || el.dataset.feature || 1);
            setTimeout(() => el.classList.add('visible'), (idx - 1) * 80);
            observer.unobserve(el);
        });
    }, { threshold: 0.10, rootMargin: '0px 0px -20px 0px' });

    document.querySelectorAll('.w-card, .w-feat-card').forEach(el => observer.observe(el));

    // 3. ANIMACIÓN ENTRADA del hero (texto)
    const heroEls = ['.w-chip','.w-hero-title','.w-hero-desc','.w-hero-btns','.w-stats'];
    heroEls.forEach((sel, i) => {
        const el = document.querySelector(sel);
        if (!el) return;
        el.style.opacity   = '0';
        el.style.transform = 'translateY(22px)';
        el.style.transition = `opacity .65s ease ${.10 + i*.09}s, transform .65s ease ${.10 + i*.09}s`;
        requestAnimationFrame(() => {
            el.style.opacity   = '1';
            el.style.transform = 'translateY(0)';
        });
    });

    // Hero imagen
    const heroImg = document.querySelector('.w-hero-img');
    if (heroImg) {
        heroImg.style.opacity   = '0';
        heroImg.style.transition = 'opacity .9s ease .55s';
        requestAnimationFrame(() => { heroImg.style.opacity = '1'; });
    }

    // 4. PARALLAX SUAVE en el hero al mover el mouse
    const heroSection = document.querySelector('.w-hero');
    if (heroSection && heroImg) {
        heroSection.addEventListener('mousemove', e => {
            const r  = heroSection.getBoundingClientRect();
            const mx = (e.clientX - r.left - r.width  / 2) / (r.width  / 2);
            const my = (e.clientY - r.top  - r.height / 2) / (r.height / 2);
            heroImg.style.transform = `translateY(0) translate(${mx*9}px, ${my*5}px)`;
        });
        heroSection.addEventListener('mouseleave', () => {
            heroImg.style.transform = '';
        });
    }

    // 5. DIMMING de tarjetas vecinas al hover
    document.querySelectorAll('.w-card').forEach(card => {
        card.addEventListener('mouseenter', () => {
            document.querySelectorAll('.w-card').forEach(c => {
                if (c !== card) c.style.opacity = '0.5';
            });
        });
        card.addEventListener('mouseleave', () => {
            document.querySelectorAll('.w-card').forEach(c => c.style.opacity = '');
        });
    });

});