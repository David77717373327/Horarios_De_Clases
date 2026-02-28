/**
 * niveles.js — Lógica específica de la vista Niveles
 */

// ── Variables globales de eliminación ──────────────────────
let _deleteId   = null;
let _deleteName = null;

// ── Inicialización ──────────────────────────────────────────
$(document).ready(function () {

    // DataTable
    initDataTable('tabla-niveles', 'buscador-niveles', {
        columnDefs: [{ orderable: false, targets: 1 }],
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json',
            emptyTable: `
                <div style="text-align:center;padding:2.5rem 1rem;">
                    <i class="fas fa-layer-group" style="font-size:2rem;opacity:0.2;display:block;margin-bottom:.75rem;color:#6b7280;"></i>
                    <p style="font-size:.9rem;font-weight:600;color:#374151;margin:0 0 4px;">No hay niveles registrados</p>
                    <small style="font-size:.8rem;color:#6b7280;">Usa el botón "Nuevo Nivel" para agregar el primero</small>
                </div>`
        }
    });

    // Auto-ocultar alerta de éxito
    autoHideAlert('alert-success', 2500);

    // Enfocar inputs al abrir offcanvas
    document.getElementById('offcanvasCrear').addEventListener('shown.bs.offcanvas', function () {
        document.getElementById('nombre-input').focus();
    });
    document.getElementById('offcanvasEditar').addEventListener('shown.bs.offcanvas', function () {
        const inp = document.getElementById('edit-nombre');
        inp.focus();
        inp.select();
    });
    document.getElementById('offcanvasEliminar').addEventListener('shown.bs.offcanvas', function () {
        const wrap = document.getElementById('delete-confirm-input-wrap');
        if (wrap && wrap.style.display !== 'none') {
            document.getElementById('delete-confirm-input').focus();
        }
    });

    // Botón submit — feedback visual
    document.getElementById('create-form').addEventListener('submit', function () {
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Guardando...';
    });
    document.getElementById('edit-form').addEventListener('submit', function () {
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Guardando...';
    });
});

// ── Offcanvas Editar ────────────────────────────────────────
function openEditCanvas(id, nombre) {
    document.getElementById('edit-nombre').value = nombre;
    document.getElementById('edit-subtitle').textContent = 'Modificando: ' + nombre;
    document.getElementById('edit-form').action = baseUrl + '/niveles/' + id;
    new bootstrap.Offcanvas(document.getElementById('offcanvasEditar')).show();
}

// ── Offcanvas Eliminar ──────────────────────────────────────
function openDeleteCanvas(id, nombre, gradosCount, descansosCount) {
    _deleteId   = id;
    _deleteName = nombre;

    document.getElementById('delete-nombre-display').textContent = nombre;

    const warnBlock   = document.getElementById('delete-warning-block');
    const confirmWrap = document.getElementById('delete-confirm-input-wrap');
    const btn         = document.getElementById('btn-confirm-delete');

    if (gradosCount > 0 || descansosCount > 0) {
        let chips = '';
        if (gradosCount > 0)
            chips += `<div class="del-consequence-chip"><i class="fas fa-chalkboard"></i><span><strong>${gradosCount}</strong> grado(s) y sus horarios</span></div>`;
        if (descansosCount > 0)
            chips += `<div class="del-consequence-chip"><i class="fas fa-coffee"></i><span><strong>${descansosCount}</strong> descanso(s)</span></div>`;
        chips += `<div class="del-consequence-chip"><i class="fas fa-link"></i><span>Todas las asignaciones académicas</span></div>`;

        warnBlock.innerHTML = `
            <div class="del-consequences-block">
                <div class="del-consequences-header">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Impacto de la eliminación</span>
                </div>
                <div class="del-consequences-chips">${chips}</div>
            </div>`;

        confirmWrap.style.display = 'block';
        const inp = document.getElementById('delete-confirm-input');
        inp.value = '';
        inp.placeholder = `Escribe "${nombre}"`;
        inp.oninput = validateDeleteInput;
        document.getElementById('del-hint-dot').style.color = '#9ca3af';
        document.getElementById('del-hint-msg').textContent  = 'Escribe el nombre exacto para continuar';
        btn.disabled = true;
        btn.classList.add('btn-danger-disabled');
    } else {
        warnBlock.innerHTML = `
            <div class="del-safe-block">
                <div class="del-safe-icon"><i class="fas fa-check-circle"></i></div>
                <div>
                    <div class="del-safe-title">Sin dependencias</div>
                    <div class="del-safe-desc">Este nivel no tiene grados ni descansos asociados. Puedes eliminarlo de forma segura.</div>
                </div>
            </div>`;
        confirmWrap.style.display = 'none';
        btn.disabled = false;
        btn.classList.remove('btn-danger-disabled');
    }

    new bootstrap.Offcanvas(document.getElementById('offcanvasEliminar')).show();
}

function validateDeleteInput() {
    const val   = document.getElementById('delete-confirm-input').value.trim();
    const btn   = document.getElementById('btn-confirm-delete');
    const dot   = document.getElementById('del-hint-dot');
    const msg   = document.getElementById('del-hint-msg');
    const match = val === _deleteName;

    if (val === '') {
        dot.style.color  = '#9ca3af';
        msg.textContent  = 'Escribe el nombre exacto para continuar';
    } else if (match) {
        dot.style.color  = '#22c55e';
        msg.textContent  = '¡Nombre confirmado! Puedes continuar.';
    } else {
        dot.style.color  = '#dc2626';
        msg.textContent  = 'El nombre no coincide, verifica e intenta de nuevo.';
    }
    btn.disabled = !match;
    btn.classList.toggle('btn-danger-disabled', !match);
}

function submitDelete() {
    const btn  = document.getElementById('btn-confirm-delete');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Eliminando...';
    const form = document.getElementById('delete-form');
    form.action = baseUrl + '/niveles/' + _deleteId;
    form.submit();
}
