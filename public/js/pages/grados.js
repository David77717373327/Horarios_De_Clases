/**
 * grados.js — Lógica específica de la vista Grados
 */

let _deleteId   = null;
let _deleteName = null;

$(document).ready(function () {

    // DataTable
    initDataTable('tabla-grados', 'buscador-grados', {
        columnDefs: [{ orderable: false, targets: 2 }],
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json',
            emptyTable: `
                <div style="text-align:center;padding:2.5rem 1rem;">
                    <i class="fas fa-chalkboard" style="font-size:2rem;opacity:0.2;display:block;margin-bottom:.75rem;color:#6b7280;"></i>
                    <p style="font-size:.9rem;font-weight:600;color:#374151;margin:0 0 4px;">No hay grados registrados</p>
                    <small style="font-size:.8rem;color:#6b7280;">Usa el botón "Nuevo Grado" para agregar el primero</small>
                </div>`
        }
    });

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

    // Feedback visual en submit
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
function openEditCanvas(id, nombre, nivelId) {
    document.getElementById('edit-nombre').value        = nombre;
    document.getElementById('edit-nivel-id').value      = nivelId;
    document.getElementById('edit-subtitle').textContent = 'Modificando: ' + nombre;
    document.getElementById('edit-form').action         = baseUrl + '/grados/' + id;
    new bootstrap.Offcanvas(document.getElementById('offcanvasEditar')).show();
}

// ── Offcanvas Eliminar ──────────────────────────────────────
function openDeleteCanvas(id, nombre, nivelNombre, horariosCount) {
    _deleteId   = id;
    _deleteName = nombre;

    document.getElementById('delete-nombre-display').textContent = nombre;
    document.getElementById('delete-nivel-display').textContent  = 'Nivel: ' + nivelNombre;

    const warnBlock   = document.getElementById('delete-warning-block');
    const confirmWrap = document.getElementById('delete-confirm-input-wrap');
    const btn         = document.getElementById('btn-confirm-delete');

    if (horariosCount > 0) {
        warnBlock.innerHTML = `
            <div class="del-consequences-block">
                <div class="del-consequences-header">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Impacto de la eliminación</span>
                </div>
                <div class="del-consequences-chips">
                    <div class="del-consequence-chip">
                        <i class="fas fa-calendar-alt"></i>
                        <span><strong>${horariosCount}</strong> horario(s) asociado(s)</span>
                    </div>
                    <div class="del-consequence-chip">
                        <i class="fas fa-link"></i>
                        <span>Todas las asignaciones relacionadas</span>
                    </div>
                </div>
            </div>`;

        confirmWrap.style.display = 'block';
        const inp = document.getElementById('delete-confirm-input');
        inp.value       = '';
        inp.placeholder = `Escribe "${nombre}"`;
        inp.oninput     = validateDeleteInput;
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
                    <div class="del-safe-desc">Este grado no tiene horarios asociados. Puedes eliminarlo de forma segura.</div>
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
        dot.style.color = '#9ca3af';
        msg.textContent = 'Escribe el nombre exacto para continuar';
    } else if (match) {
        dot.style.color = '#22c55e';
        msg.textContent = '¡Nombre confirmado! Puedes continuar.';
    } else {
        dot.style.color = '#dc2626';
        msg.textContent = 'El nombre no coincide, verifica e intenta de nuevo.';
    }
    btn.disabled = !match;
    btn.classList.toggle('btn-danger-disabled', !match);
}

function submitDelete() {
    const btn  = document.getElementById('btn-confirm-delete');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Eliminando...';
    const form = document.getElementById('delete-form');
    form.action = baseUrl + '/grados/' + _deleteId;
    form.submit();
}
