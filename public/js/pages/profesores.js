/**
 * profesores.js — Lógica específica de la vista Profesores
 */

let _deleteId   = null;
let _deleteName = null;
let _editId     = null;
let _asignarId  = null;

// ── Helper: cerrar offcanvas de forma segura ─────────────────
function hideOffcanvas(id) {
    const el  = document.getElementById(id);
    const ins = bootstrap.Offcanvas.getInstance(el) ?? new bootstrap.Offcanvas(el);
    ins.hide();
}

// ── Inicialización ──────────────────────────────────────────
$(document).ready(function () {

    initDataTable('tabla-profesores', 'buscador-profesores', {
        columnDefs: [{ orderable: false, targets: [1, 2] }],
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json',
            emptyTable: `
                <div style="text-align:center;padding:2.5rem 1rem;">
                    <i class="fas fa-user-tie" style="font-size:2rem;opacity:0.2;display:block;margin-bottom:.75rem;color:#6b7280;"></i>
                    <p style="font-size:.9rem;font-weight:600;color:#374151;margin:0 0 4px;">No hay profesores registrados</p>
                    <small style="font-size:.8rem;color:#6b7280;">Usa el botón "Nuevo Profesor" para agregar el primero</small>
                </div>`
        }
    });

    autoHideAlert('alert-success', 2500);

    document.getElementById('offcanvasCrear').addEventListener('shown.bs.offcanvas', function () {
        document.getElementById('crear-nombre').focus();
    });
    document.getElementById('offcanvasCrear').addEventListener('hidden.bs.offcanvas', function () {
        limpiarFormCrear();
    });
    document.getElementById('offcanvasEditar').addEventListener('shown.bs.offcanvas', function () {
        const inp = document.getElementById('edit-nombre');
        inp.focus(); inp.select();
    });
    document.getElementById('offcanvasEliminar').addEventListener('shown.bs.offcanvas', function () {
        const wrap = document.getElementById('delete-confirm-input-wrap');
        if (wrap && wrap.style.display !== 'none')
            document.getElementById('delete-confirm-input').focus();
    });
});

// ── Helpers ─────────────────────────────────────────────────

function setBtnLoading(btnId, texto = 'Guardando...') {
    const btn = document.getElementById(btnId);
    if (!btn) return;
    btn._original = btn.innerHTML;
    btn.disabled  = true;
    btn.innerHTML = `<i class="fas fa-spinner fa-spin me-1"></i> ${texto}`;
}

function resetBtn(btnId) {
    const btn = document.getElementById(btnId);
    if (!btn || !btn._original) return;
    btn.disabled  = false;
    btn.innerHTML = btn._original;
}

function mostrarAlertaDinamica(tipo, mensaje) {
    // Usa el contenedor fijo definido en el Blade, justo debajo del header
    const container = document.getElementById('alert-dynamic-container');
    if (!container) return;

    const isSuccess = tipo === 'success';
    const id  = 'alert-dyn-' + Date.now();
    const div = document.createElement('div');
    div.className = `alert-preline alert-preline-${tipo}`;
    div.id = id;
    div.setAttribute('role', 'alert');
    div.innerHTML = `
        <div class="alert-preline-icon-wrap alert-preline-icon-${tipo}">
            <svg class="alert-preline-svg" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                ${isSuccess
                    ? '<path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><path d="m9 12 2 2 4-4"/>'
                    : '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>'}
            </svg>
        </div>
        <div class="alert-preline-body">
            <h3 class="alert-preline-title">${isSuccess ? 'Operación exitosa' : 'Se produjo un error'}</h3>
            <p class="alert-preline-msg">${mensaje}</p>
        </div>
        <button class="alert-preline-close" onclick="hideAlert('${id}')" aria-label="Cerrar">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.innerHTML = '';
    container.appendChild(div);
    autoHideAlert(id, 3500);
}

function mostrarError(errorDivId, mensaje) {
    const wrap = document.getElementById(errorDivId);
    if (!wrap) return;
    wrap.querySelector('span').textContent = mensaje;
    wrap.style.display = 'block';
}

function limpiarError(errorDivId) {
    const wrap = document.getElementById(errorDivId);
    if (!wrap) return;
    wrap.querySelector('span').textContent = '';
    wrap.style.display = 'none';
}

function limpiarFormCrear() {
    document.getElementById('crear-nombre').value = '';
    document.querySelectorAll('#crear-asignaturas-list input[type="checkbox"]')
        .forEach(cb => cb.checked = false);
    limpiarError('error-crear-nombre');
    resetBtn('btn-crear');
}

function buildCheckboxGrid(containerId, todasAsigs, seleccionadas = []) {
    const container = document.getElementById(containerId);
    if (!container) return;
    container.innerHTML = '';
    todasAsigs.forEach(asignatura => {
        const label = document.createElement('label');
        label.className = 'checkbox-label-grid';
        label.innerHTML = `
            <input type="checkbox" name="asignaturas[]"
                value="${asignatura.id}"
                ${seleccionadas.includes(asignatura.id) ? 'checked' : ''}>
            <span>${asignatura.nombre}</span>
        `;
        container.appendChild(label);
    });
}

function getCheckedIds(containerId) {
    return Array.from(
        document.querySelectorAll(`#${containerId} input[type="checkbox"]:checked`)
    ).map(cb => parseInt(cb.value));
}

function buildAsignaturasHTML(asignaturas) {
    if (!asignaturas || asignaturas.length === 0) {
        return `<span class="badge-nivel" style="opacity:0.6;font-style:italic;">
                    <i class="fas fa-exclamation-circle me-1"></i>Sin asignaturas
                </span>`;
    }
    return asignaturas
        .map(a => `<span class="badge-nivel"><i class="fas fa-book me-1"></i>${a.nombre}</span>`)
        .join('');
}

function actualizarFilaTabla(profesor) {
    const tr = document.querySelector(`tr[data-id="${profesor.id}"]`);
    if (!tr) return;
    const nombreEscapado = profesor.name.replace(/'/g, "\\'");
    const asigCount      = profesor.asignaturas ? profesor.asignaturas.length : 0;

    tr.querySelector('.cell-title').textContent = profesor.name;
    tr.querySelector('td:nth-child(2)').innerHTML =
        `<div class="d-flex flex-wrap gap-1">${buildAsignaturasHTML(profesor.asignaturas)}</div>`;
    tr.querySelector('.btn-icon-green').setAttribute('onclick',
        `openAsignarCanvas(${profesor.id}, '${nombreEscapado}')`);
    tr.querySelector('.btn-icon-blue').setAttribute('onclick',
        `openEditCanvas(${profesor.id}, '${nombreEscapado}')`);
    tr.querySelector('.btn-icon-red').setAttribute('onclick',
        `openDeleteCanvas(${profesor.id}, '${nombreEscapado}', ${asigCount})`);
}

function agregarFilaTabla(profesor) {
    const tbody          = document.querySelector('#tabla-profesores tbody');
    const nombreEscapado = profesor.name.replace(/'/g, "\\'");
    const asigCount      = profesor.asignaturas ? profesor.asignaturas.length : 0;
    const tr             = document.createElement('tr');
    tr.setAttribute('data-id', profesor.id);
    tr.innerHTML = `
        <td>
            <div class="cell-with-icon">
                <div class="cell-icon"><i class="fas fa-user-tie"></i></div>
                <div>
                    <span class="cell-title">${profesor.name}</span>
                    <span class="cell-subtitle">Profesor</span>
                </div>
            </div>
        </td>
        <td><div class="d-flex flex-wrap gap-1">${buildAsignaturasHTML(profesor.asignaturas)}</div></td>
        <td>
            <div class="actions-group">
                <button class="btn-icon btn-icon-green"
                    onclick="openAsignarCanvas(${profesor.id}, '${nombreEscapado}')"
                    title="Asignar asignaturas">
                    <i class="fas fa-book"></i>
                </button>
                <button class="btn-icon btn-icon-blue"
                    onclick="openEditCanvas(${profesor.id}, '${nombreEscapado}')"
                    title="Editar">
                    <i class="fas fa-pen"></i>
                </button>
                <button class="btn-icon btn-icon-red"
                    onclick="openDeleteCanvas(${profesor.id}, '${nombreEscapado}', ${asigCount})"
                    title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </td>
    `;
    tbody.insertBefore(tr, tbody.firstChild);
}

// ── Fetch show ──────────────────────────────────────────────

async function fetchProfesor(id) {
    const response = await fetch(`${baseUrl}/profesores/${id}`, {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
    });
    if (!response.ok) throw new Error(`HTTP ${response.status}`);
    const result = await response.json();
    if (!result.success) throw new Error(result.message || 'Error del servidor');
    return result.profesor;
}

// ── Offcanvas Crear ─────────────────────────────────────────

async function crearProfesor() {
    limpiarError('error-crear-nombre');
    const nombre = document.getElementById('crear-nombre').value.trim();
    if (!nombre) {
        mostrarError('error-crear-nombre', 'El nombre del profesor es obligatorio.');
        return;
    }
    setBtnLoading('btn-crear');
    try {
        const response = await fetch(`${baseUrl}/profesores`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                name: nombre,
                asignaturas: getCheckedIds('crear-asignaturas-list')
            })
        });
        const result = await response.json();
        if (response.ok && result.success) {
            hideOffcanvas('offcanvasCrear');
            resetBtn('btn-crear');
            mostrarAlertaDinamica('success', result.message);
            agregarFilaTabla(result.profesor);
        } else {
            if (result.errors?.name) mostrarError('error-crear-nombre', result.errors.name[0]);
            else mostrarAlertaDinamica('error', result.message || 'Error al crear el profesor.');
            resetBtn('btn-crear');
        }
    } catch (e) {
        console.error('crearProfesor:', e);
        mostrarAlertaDinamica('error', 'Error de conexión. Intenta nuevamente.');
        resetBtn('btn-crear');
    }
}

// ── Offcanvas Editar ────────────────────────────────────────

async function openEditCanvas(id, nombre) {
    _editId = id;
    document.getElementById('edit-nombre').value         = nombre;
    document.getElementById('edit-subtitle').textContent = 'Modificando: ' + nombre;
    limpiarError('error-edit-nombre');

    document.getElementById('edit-asignaturas-list').innerHTML =
        `<div style="grid-column:1/-1;text-align:center;padding:1.5rem;color:#6b7280;font-size:0.82rem;">
            <i class="fas fa-spinner fa-spin me-2"></i>Cargando asignaturas...
        </div>`;

    const el = document.getElementById('offcanvasEditar');
    (bootstrap.Offcanvas.getInstance(el) ?? new bootstrap.Offcanvas(el)).show();

    try {
        const profesor      = await fetchProfesor(id);
        const seleccionadas = profesor.asignaturas.map(a => a.id);
        buildCheckboxGrid('edit-asignaturas-list', todasAsignaturas, seleccionadas);
    } catch (e) {
        console.error('openEditCanvas fetch:', e);
        buildCheckboxGrid('edit-asignaturas-list', todasAsignaturas, []);
    }
}

async function actualizarProfesor() {
    limpiarError('error-edit-nombre');
    const nombre = document.getElementById('edit-nombre').value.trim();
    if (!nombre) {
        mostrarError('error-edit-nombre', 'El nombre del profesor es obligatorio.');
        return;
    }
    setBtnLoading('btn-editar');
    try {
        const response = await fetch(`${baseUrl}/profesores/${_editId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                name: nombre,
                asignaturas: getCheckedIds('edit-asignaturas-list')
            })
        });
        const result = await response.json();
        if (response.ok && result.success) {
            hideOffcanvas('offcanvasEditar');
            resetBtn('btn-editar');
            mostrarAlertaDinamica('success', result.message);
            actualizarFilaTabla(result.profesor);
        } else {
            if (result.errors?.name) mostrarError('error-edit-nombre', result.errors.name[0]);
            else mostrarAlertaDinamica('error', result.message || 'Error al actualizar.');
            resetBtn('btn-editar');
        }
    } catch (e) {
        console.error('actualizarProfesor:', e);
        mostrarAlertaDinamica('error', 'Error de conexión. Intenta nuevamente.');
        resetBtn('btn-editar');
    }
}

// ── Offcanvas Asignar Asignaturas ───────────────────────────

async function openAsignarCanvas(id, nombre) {
    _asignarId = id;
    document.getElementById('asignar-subtitle').textContent = 'Prof: ' + nombre;

    document.getElementById('asignar-asignaturas-list').innerHTML =
        `<div style="grid-column:1/-1;text-align:center;padding:1.5rem;color:#6b7280;font-size:0.82rem;">
            <i class="fas fa-spinner fa-spin me-2"></i>Cargando asignaturas...
        </div>`;

    const el = document.getElementById('offcanvasAsignar');
    (bootstrap.Offcanvas.getInstance(el) ?? new bootstrap.Offcanvas(el)).show();

    try {
        const profesor      = await fetchProfesor(id);
        const seleccionadas = profesor.asignaturas.map(a => a.id);
        buildCheckboxGrid('asignar-asignaturas-list', todasAsignaturas, seleccionadas);
    } catch (e) {
        console.error('openAsignarCanvas fetch:', e);
        buildCheckboxGrid('asignar-asignaturas-list', todasAsignaturas, []);
    }
}

async function guardarAsignaturas() {
    setBtnLoading('btn-asignar');
    try {
        const response = await fetch(`${baseUrl}/profesores/${_asignarId}/asignar-asignaturas`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                asignaturas: getCheckedIds('asignar-asignaturas-list')
            })
        });
        const result = await response.json();
        if (response.ok && result.success) {
            hideOffcanvas('offcanvasAsignar');
            resetBtn('btn-asignar');
            mostrarAlertaDinamica('success', result.message);
            actualizarFilaTabla(result.profesor);
        } else {
            mostrarAlertaDinamica('error', result.message || 'Error al guardar las asignaturas.');
            resetBtn('btn-asignar');
        }
    } catch (e) {
        console.error('guardarAsignaturas:', e);
        mostrarAlertaDinamica('error', 'Error de conexión. Intenta nuevamente.');
        resetBtn('btn-asignar');
    }
}

// ── Offcanvas Eliminar ──────────────────────────────────────

function openDeleteCanvas(id, nombre, asigCount) {
    _deleteId   = id;
    _deleteName = nombre;

    document.getElementById('delete-nombre-display').textContent = nombre;

    const warnBlock   = document.getElementById('delete-warning-block');
    const confirmWrap = document.getElementById('delete-confirm-input-wrap');
    const btn         = document.getElementById('btn-confirm-delete');

    if (asigCount > 0) {
        warnBlock.innerHTML = `
            <div class="del-consequences-block">
                <div class="del-consequences-header">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Impacto de la eliminación</span>
                </div>
                <div class="del-consequences-chips">
                    <div class="del-consequence-chip">
                        <i class="fas fa-book"></i>
                        <span><strong>${asigCount}</strong> asignatura(s) asignada(s)</span>
                    </div>
                    <div class="del-consequence-chip">
                        <i class="fas fa-link"></i>
                        <span>Todas las asignaciones relacionadas</span>
                    </div>
                </div>
            </div>`;
        confirmWrap.style.display = 'block';
        const inp       = document.getElementById('delete-confirm-input');
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
                    <div class="del-safe-desc">Este profesor no tiene asignaturas asignadas. Puedes eliminarlo de forma segura.</div>
                </div>
            </div>`;
        confirmWrap.style.display = 'none';
        btn.disabled = false;
        btn.classList.remove('btn-danger-disabled');
    }

    const el = document.getElementById('offcanvasEliminar');
    (bootstrap.Offcanvas.getInstance(el) ?? new bootstrap.Offcanvas(el)).show();
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

async function submitDelete() {
    setBtnLoading('btn-confirm-delete', 'Eliminando...');
    try {
        const response = await fetch(`${baseUrl}/profesores/${_deleteId}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        });
        const result = await response.json();
        if (response.ok && result.success) {
            hideOffcanvas('offcanvasEliminar');
            resetBtn('btn-confirm-delete');
            mostrarAlertaDinamica('success', result.message);
            document.querySelector(`tr[data-id="${_deleteId}"]`)?.remove();
        } else {
            mostrarAlertaDinamica('error', result.message || 'Error al eliminar el profesor.');
            resetBtn('btn-confirm-delete');
        }
    } catch (e) {
        console.error('submitDelete:', e);
        mostrarAlertaDinamica('error', 'Error de conexión. Intenta nuevamente.');
        resetBtn('btn-confirm-delete');
    }
}