/**
 * login.js — Gimnasio Humanístico del Alto Magdalena
 * Incluye: carrusel 3D automático · toggle contraseña · captcha · validación
 */

document.addEventListener('DOMContentLoaded', () => {

    /* ─────────────────────────────────────────
       1. CARRUSEL 3D AUTOMÁTICO
    ───────────────────────────────────────── */
    const slides     = document.querySelectorAll('.slide-3d');
    const dots       = document.querySelectorAll('.dot');
    let current      = 0;
    let autoInterval = null;
    const DELAY      = 3500; // ms entre slides

    function goToSlide(next) {
        if (next === current || slides.length === 0) return;

        // Salida del slide actual
        slides[current].classList.add('exit');
        slides[current].classList.remove('active');
        dots[current]?.classList.remove('active');

        // Limpiar clase exit tras la animación
        const exiting = slides[current];
        setTimeout(() => exiting.classList.remove('exit'), 650);

        // Entrada del nuevo slide
        current = next;
        slides[current].classList.add('active');
        dots[current]?.classList.add('active');
    }

    function nextSlide() {
        const next = (current + 1) % slides.length;
        goToSlide(next);
    }

    function startAuto() {
        stopAuto();
        if (slides.length > 1) {
            autoInterval = setInterval(nextSlide, DELAY);
        }
    }

    function stopAuto() {
        if (autoInterval) { clearInterval(autoInterval); autoInterval = null; }
    }

    // Puntos de navegación
    dots.forEach((dot, i) => {
        dot.addEventListener('click', () => {
            goToSlide(i);
            stopAuto();
            startAuto(); // reinicia el timer al hacer clic
        });
    });

    // Swipe táctil en el carrusel
    const carousel = document.querySelector('.carousel-3d');
    if (carousel) {
        let touchStartX = 0;

        carousel.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].clientX;
        }, { passive: true });

        carousel.addEventListener('touchend', (e) => {
            const diff = touchStartX - e.changedTouches[0].clientX;
            if (Math.abs(diff) > 40) {
                stopAuto();
                goToSlide(diff > 0
                    ? (current + 1) % slides.length
                    : (current - 1 + slides.length) % slides.length
                );
                startAuto();
            }
        }, { passive: true });
    }

    // Iniciar carrusel
    startAuto();

    /* ─────────────────────────────────────────
       2. TOGGLE CONTRASEÑA
    ───────────────────────────────────────── */
    const toggleBtn   = document.getElementById('togglePassword');
    const passwordInp = document.getElementById('password');

    if (toggleBtn && passwordInp) {
        toggleBtn.addEventListener('click', () => {
            const hidden = passwordInp.type === 'password';
            passwordInp.type = hidden ? 'text' : 'password';
            const icon = toggleBtn.querySelector('i');
            icon.classList.toggle('fa-eye',       !hidden);
            icon.classList.toggle('fa-eye-slash',  hidden);
            toggleBtn.style.transform = 'scale(0.88)';
            setTimeout(() => { toggleBtn.style.transform = ''; }, 150);
        });
    }

    /* ─────────────────────────────────────────
       3. CAPTCHA SIMULADO
    ───────────────────────────────────────── */
    const captchaBox      = document.getElementById('captchaBox');
    const captchaCheckbox = document.getElementById('captchaCheckbox');
    const captchaIcon     = document.getElementById('captchaCheckIcon');
    const captchaLabel    = document.getElementById('captchaLabel');
    const captchaHidden   = document.getElementById('captchaVerified');

    let captchaVerified = false;
    let captchaLoading  = false;

    const activateCaptcha = () => {
        if (captchaVerified || captchaLoading) return;
        captchaLoading = true;
        captchaIcon.className = 'fas fa-spinner captcha-loading';
        captchaIcon.style.opacity = '1';
        captchaIcon.style.transform = 'scale(1)';
        captchaCheckbox.style.borderColor = '#3b82f6';
        captchaLabel.textContent = 'Verificando...';

        setTimeout(() => {
            captchaVerified = true;
            captchaLoading  = false;
            captchaCheckbox.classList.add('checked');
            captchaIcon.className = 'fas fa-check';
            captchaLabel.textContent = 'No soy un robot';
            captchaBox.classList.add('verified');
            if (captchaHidden) captchaHidden.value = '1';
            captchaBox.style.transform = 'scale(1.025)';
            setTimeout(() => { captchaBox.style.transform = ''; }, 200);
            const errEl = captchaBox.closest('.captcha-group')?.querySelector('.invalid-feedback');
            if (errEl) errEl.remove();
        }, 1300);
    };

    if (captchaBox) {
        captchaBox.addEventListener('click', activateCaptcha);
        captchaBox.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); activateCaptcha(); }
        });
    }

    /* ─────────────────────────────────────────
       4. VALIDACIÓN DEL FORMULARIO
    ───────────────────────────────────────── */
    const loginForm = document.getElementById('loginForm');
    const submitBtn = document.getElementById('submitBtn');

    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            clearErrors();
            let isValid = true;
            const email    = document.getElementById('email');
            const password = document.getElementById('password');

            if (!email.value.trim()) {
                showInputError(email, 'El correo electrónico es requerido.');
                isValid = false;
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
                showInputError(email, 'Ingresa un correo electrónico válido.');
                isValid = false;
            }

            if (!password.value) {
                showInputError(password, 'La contraseña es requerida.');
                isValid = false;
            } else if (password.value.length < 6) {
                showInputError(password, 'La contraseña debe tener al menos 6 caracteres.');
                isValid = false;
            }

            if (!captchaVerified) {
                e.preventDefault();
                showGroupError('.captcha-group', 'Por favor, confirma que no eres un robot.');
                shake(captchaBox);
                return;
            }

            if (!isValid) { e.preventDefault(); return; }

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>&nbsp; Ingresando...';
            }
        });
    }

    /* ─────────────────────────────────────────
       5. HELPERS
    ───────────────────────────────────────── */
    function showInputError(input, msg) {
        input.classList.add('is-invalid');
        const group = input.closest('.form-group');
        let fb = group.querySelector('.invalid-feedback');
        if (!fb) {
            fb = document.createElement('span');
            fb.className = 'invalid-feedback';
            fb.setAttribute('role', 'alert');
            group.appendChild(fb);
        }
        fb.innerHTML = `<strong>${msg}</strong>`;
        fb.style.display = 'block';
    }

    function showGroupError(selector, msg) {
        const group = document.querySelector(selector);
        if (!group) return;
        let fb = group.querySelector('.invalid-feedback');
        if (!fb) {
            fb = document.createElement('span');
            fb.className = 'invalid-feedback';
            fb.setAttribute('role', 'alert');
            group.appendChild(fb);
        }
        fb.innerHTML = `<strong>${msg}</strong>`;
        fb.style.display = 'block';
    }

    function clearErrors() {
        document.querySelectorAll('.invalid-feedback').forEach(el => { el.style.display='none'; el.textContent=''; });
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    }

    function shake(el) {
        if (!el) return;
        if (!document.getElementById('shakeKF')) {
            const s = document.createElement('style');
            s.id = 'shakeKF';
            s.textContent = `@keyframes shake {
                0%,100%{transform:translateX(0)}
                20%{transform:translateX(-7px)}
                40%{transform:translateX(7px)}
                60%{transform:translateX(-4px)}
                80%{transform:translateX(4px)}
            }`;
            document.head.appendChild(s);
        }
        el.style.animation = 'none';
        el.offsetHeight;
        el.style.animation = 'shake 0.42s ease';
        setTimeout(() => { el.style.animation = ''; }, 440);
    }

    /* ─────────────────────────────────────────
       6. LIMPIAR ERROR AL ESCRIBIR
    ───────────────────────────────────────── */
    document.querySelectorAll('.input-field').forEach(inp => {
        inp.addEventListener('input', () => {
            inp.classList.remove('is-invalid');
            const fb = inp.closest('.form-group')?.querySelector('.invalid-feedback');
            if (fb) { fb.style.display='none'; fb.textContent=''; }
        });
    });

    /* ─────────────────────────────────────────
       7. ANIMACIÓN ENTRADA ESCALONADA (panel derecho)
    ───────────────────────────────────────── */
    document.querySelectorAll(
        '.form-group, .captcha-group, .form-actions, .additional-links'
    ).forEach((el, i) => {
        el.style.opacity   = '0';
        el.style.transform = 'translateY(16px)';
        setTimeout(() => {
            el.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            el.style.opacity    = '1';
            el.style.transform  = 'translateY(0)';
        }, 180 + i * 90);
    });

});