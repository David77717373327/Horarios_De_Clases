/**
 * datatables.js — Configuración base de DataTables
 * Reutilizable en todas las vistas
 */
function initDataTable(tableId, searchInputId, options = {}) {
    const defaults = {
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json',
        },
        pageLength: 10,
        order: [[0, 'asc']],
        dom: 'rt',
        initComplete: function () {
            $('#dt-info').append($('.dataTables_info'));
            $('#dt-paginate').append($('.dataTables_paginate'));
        }
    };

    const config = Object.assign({}, defaults, options);
    const dt = $('#' + tableId).DataTable(config);

    if (searchInputId) {
        $('#' + searchInputId).on('keyup', function () {
            dt.search(this.value).draw();
        });
    }

    return dt;
}
