/**
 * asignaturas.js — Lógica premium con cards dinámicas
 */

let _deleteId   = null;
let _deleteName = null;
let fieldCount  = 0;

// ── Inicialización ──────────────────────────────────────────
$(document).ready(function () {

    initDataTable('tabla-asignaturas', 'buscador-asignaturas', {
        columnDefs: [{ orderable: false, targets: 1 }],
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json',
            emptyTable: `
                <div style="text-align:center;padding:2.5rem 1rem;">
                    <i class="fas fa-book" style="font-size:2rem;opacity:0.2;display:block;margin-bottom:.75rem;color:#6b7280;"></i>
                    <p style="font-size:.9rem;font-weight:600;color:#374151;margin:0 0 4px;">No hay asignaturas registradas</p>
                    <small style="font-size:.8rem;color:#6b7280;">Usa el botón "Nueva Asignatura" para agregar la primera</small>
                </div>`
        }
    });

    autoHideAlert('alert-success', 2500);

    document.getElementById('offcanvasCrear').addEventListener('show.bs.offcanvas', resetFields);
    document.getElementById('offcanvasCrear').addEventListener('shown.bs.offcanvas', focusLastField);

    document.getElementById('offcanvasEditar').addEventListener('shown.bs.offcanvas', function () {
        const inp = document.getElementById('edit-nombre');
        inp.focus(); inp.select();
    });

    document.getElementById('offcanvasEliminar').addEventListener('shown.bs.offcanvas', function () {
        const wrap = document.getElementById('delete-confirm-input-wrap');
        if (wrap && wrap.style.display !== 'none')
            document.getElementById('delete-confirm-input').focus();
    });

    document.getElementById('create-form').addEventListener('submit', function () {
        // Quitar campos vacíos antes de enviar
        document.querySelectorAll('.mf-input').forEach(inp => {
            if (!inp.value.trim()) inp.closest('.multi-field-card')?.remove();
        });
        const btn = document.getElementById('btn-submit-create');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Guardando...';
    });

    document.getElementById('edit-form').addEventListener('submit', function () {
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Guardando...';
    });
});

// ── Sistema de cards dinámicas ──────────────────────────────

function resetFields() {
    fieldCount = 0;
    document.getElementById('fields-container').innerHTML = '';
    addField();
}

function addField(focusNew = false) {
    fieldCount++;
    const container = document.getElementById('fields-container');
    const cardId    = 'card-' + Date.now() + '-' + fieldCount;
    const inputId   = 'field-' + fieldCount;
    const isFirst   = document.querySelectorAll('.multi-field-card').length === 0;

    const card = document.createElement('div');
    card.className = 'multi-field-card';
    card.id        = cardId;
    card.innerHTML = `
        <div class="multi-field-number">${document.querySelectorAll('.multi-field-card').length + 1}</div>

        <div class="mf-input-wrap">
            <i class="fas fa-book mf-icon"></i>
            <input
                type="text"
                name="nombres[]"
                id="${inputId}"
                class="mf-input"
                placeholder="Ej: Matemáticas, Español..."
                autocomplete="off"
            >
        </div>

        <div class="mf-status-icon" id="status-${cardId}">
            <i class="fas fa-check"></i>
        </div>

        ${!isFirst
            ? `<button type="button" class="mf-remove-btn" onclick="removeCard('${cardId}')" title="Quitar">
                <i class="fas fa-times"></i>
               </button>`
            : `<div style="width:28px;flex-shrink:0;"></div>`
        }
    `;

    container.appendChild(card);

    // Animar entrada
    requestAnimationFrame(() => {
        requestAnimationFrame(() => card.classList.add('multi-field-card--visible'));
    });

    // Eventos del input
    const input = card.querySelector('.mf-input');

    input.addEventListener('input', function () {
        updateCardState(card, cardId, this.value);
        updateCounter();
    });

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addField(true);
        }
        if (e.key === 'Backspace' && this.value === '' && !isFirst) {
            e.preventDefault();
            removeCard(cardId);
        }
    });

    updateCounter();
    if (focusNew) setTimeout(() => input.focus(), 50);
}

function updateCardState(card, cardId, value) {
    const filled     = value.trim() !== '';
    const statusIcon = document.getElementById('status-' + cardId);
    const numEl      = card.querySelector('.multi-field-number');

    // Detectar duplicado dentro del formulario
    const allValues = Array.from(document.querySelectorAll('.mf-input'))
        .map(i => i.value.trim().toLowerCase())
        .filter(v => v !== '');
    const isDuplicate = filled && allValues.filter(v => v === value.trim().toLowerCase()).length > 1;

    card.classList.toggle('is-filled', filled && !isDuplicate);

    if (filled && !isDuplicate) {
        statusIcon.className = 'mf-status-icon valid show';
        statusIcon.innerHTML = '<i class="fas fa-check"></i>';
    } else if (isDuplicate) {
        statusIcon.className = 'mf-status-icon duplicate show';
        statusIcon.innerHTML = '<i class="fas fa-exclamation"></i>';
        card.classList.remove('is-filled');
        card.style.borderColor = '#fca5a5';
        card.style.background  = '#fef2f2';
    } else {
        statusIcon.className = 'mf-status-icon';
        card.style.borderColor = '';
        card.style.background  = '';
    }

    if (!isDuplicate) {
        card.style.borderColor = '';
        card.style.background  = '';
    }
}

function removeCard(cardId) {
    const card = document.getElementById(cardId);
    if (!card) return;

    card.classList.add('multi-field-card--removing');
    setTimeout(() => {
        card.remove();
        renumberCards();
        updateCounter();
        if (document.querySelectorAll('.multi-field-card').length === 0) {
            fieldCount = 0;
            addField();
        }
    }, 230);
}

function renumberCards() {
    document.querySelectorAll('.multi-field-card').forEach((card, index) => {
        const num = card.querySelector('.multi-field-number');
        if (num) num.textContent = index + 1;
    });
}

function focusLastField() {
    const inputs = document.querySelectorAll('.mf-input');
    if (inputs.length > 0) inputs[inputs.length - 1].focus();
}

function updateCounter() {
    const inputs   = document.querySelectorAll('.mf-input');
    const filled   = Array.from(inputs).filter(i => i.value.trim() !== '').length;
    const total    = inputs.length;

    // Subtítulo offcanvas
    document.getElementById('oc-create-subtitle').textContent =
        total === 1 ? 'Agrega una o varias asignaturas' : `${total} campos — ${filled} con nombre`;

    // Badge contador
    document.getElementById('field-count-label').textContent =
        total === 1 ? '1 asignatura' : `${total} asignaturas`;

    // Resumen en footer
    const summary    = document.getElementById('mf-summary');
    const summaryTxt = document.getElementById('mf-summary-text');
    if (filled === 0) {
        summary.classList.remove('has-items');
        summaryTxt.textContent = 'Completa al menos un campo para guardar';
    } else {
        summary.classList.add('has-items');
        summaryTxt.textContent = filled === 1
            ? '1 asignatura lista para guardar'
            : `${filled} asignaturas listas para guardar`;
    }

    // Botón submit
    document.getElementById('btn-submit-label').textContent = filled === 0
        ? 'Guardar asignatura'
        : filled === 1 ? 'Guardar 1 asignatura' : `Guardar ${filled} asignaturas`;
}

// ── Offcanvas Editar ────────────────────────────────────────
function openEditCanvas(id, nombre) {
    document.getElementById('edit-nombre').value         = nombre;
    document.getElementById('edit-subtitle').textContent = 'Modificando: ' + nombre;
    document.getElementById('edit-form').action          = baseUrl + '/asignaturas/' + id;
    new bootstrap.Offcanvas(document.getElementById('offcanvasEditar')).show();
}

// ── Offcanvas Eliminar ──────────────────────────────────────
function openDeleteCanvas(id, nombre, horariosCount) {
    _deleteId   = id;
    _deleteName = nombre;

    document.getElementById('delete-nombre-display').textContent = nombre;

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
                    <div class="del-safe-desc">Esta asignatura no tiene horarios asociados. Puedes eliminarla de forma segura.</div>
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
    form.action = baseUrl + '/asignaturas/' + _deleteId;
    form.submit();
}
