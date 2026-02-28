/**
 * alerts.js — Manejo global de alertas
 * Reutilizable en todas las vistas
 */
function hideAlert(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.classList.add('hiding');
    setTimeout(() => el.remove(), 420);
}

function autoHideAlert(id, delay = 2500) {
    setTimeout(() => hideAlert(id), delay);
}
