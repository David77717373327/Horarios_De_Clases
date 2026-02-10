@extends('layouts.master')

@section('content')
<div class="container py-4">
    {{-- Header con título y descripción --}}
    <div class="mb-4">
        <h2 class="fw-bold mb-1" style="color: #000000;">Gestión de Grados</h2>
        <p class="text-muted mb-0" style="font-size: 0.95rem;">Administra los grados académicos por nivel educativo</p>
    </div>

    {{-- Mensajes de éxito --}}
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4" style="border-left: 4px solid #10b981 !important;">
            <div class="d-flex align-items-center">
                <svg width="20" height="20" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                </svg>
                {{ session('success') }}
            </div>
        </div>
    @endif

    {{-- Formulario inline arriba de la tabla --}}
    <div class="card border-0 shadow-sm mb-4" style="border: 1px solid #e5e7eb;">
        <div class="card-body p-4">
            <div class="d-flex align-items-center mb-3">
                <div class="d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; background-color: #eff6ff; border-radius: 8px;">
                    <svg width="20" height="20" fill="#1e40af" viewBox="0 0 16 16">
                        <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                    </svg>
                </div>
                <div>
                    <h5 class="mb-0 fw-semibold" style="color: #000000; font-size: 1.05rem;">Crear Nuevo Grado</h5>
                    <small class="text-muted" style="font-size: 0.85rem;">Complete los campos para registrar un grado</small>
                </div>
            </div>

            <form method="POST" action="{{ route('grados.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label fw-medium mb-2" style="color: #374151; font-size: 0.875rem;">
                            Nombre del Grado
                        </label>
                        <input 
                            type="text" 
                            name="nombre" 
                            class="form-control @error('nombre') is-invalid @enderror" 
                            placeholder="Ej: Sexto 601, Primero A"
                            value="{{ old('nombre') }}"
                            style="border: 1px solid #d1d5db; padding: 0.625rem 0.875rem; font-size: 0.95rem;"
                            required
                            autofocus>
                        @error('nombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-medium mb-2" style="color: #374151; font-size: 0.875rem;">
                            Nivel Académico
                        </label>
                        <select 
                            name="nivel_id" 
                            class="form-select @error('nivel_id') is-invalid @enderror" 
                            style="border: 1px solid #d1d5db; padding: 0.625rem 0.875rem; font-size: 0.95rem;"
                            required>
                            <option value="">Seleccionar...</option>
                            @foreach($niveles as $nivel)
                                <option value="{{ $nivel->id }}" {{ old('nivel_id') == $nivel->id ? 'selected' : '' }}>
                                    {{ $nivel->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('nivel_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3 d-flex align-items-end">
                        <button 
                            type="submit" 
                            class="btn btn-primary-custom w-100 fw-semibold d-flex align-items-center justify-content-center" 
                            style="background-color: #1e40af; color: #ffffff; padding: 0.625rem 1rem; border: none; font-size: 0.95rem; height: 42px;">
                            <svg width="18" height="18" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                                <path d="M2 1a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H9.5a1 1 0 0 0-1 1v7.293l2.646-2.647a.5.5 0 0 1 .708.708l-3.5 3.5a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L7.5 9.293V2a2 2 0 0 1 2-2H14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h2.5a.5.5 0 0 1 0 1H2z"/>
                            </svg>
                            Guardar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabla full width --}}
    <div class="card border-0 shadow-sm" style="border: 1px solid #e5e7eb;">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center" style="border-color: #e5e7eb !important; padding: 1.25rem 1.5rem;">
            <div>
                <h5 class="mb-0 fw-semibold" style="color: #000000; font-size: 1.05rem;">Grados Registrados</h5>
                <small class="text-muted" style="font-size: 0.85rem;">{{ $grados->count() }} grados en el sistema</small>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" style="font-size: 0.95rem;">
                    <thead style="background-color: #f9fafb; border-bottom: 2px solid #e5e7eb;">
                        <tr>
                            <th class="fw-semibold" style="color: #374151; padding: 1rem 1.5rem; width: 80px;">ID</th>
                            <th class="fw-semibold" style="color: #374151; padding: 1rem 1.5rem;">Nombre del Grado</th>
                            <th class="fw-semibold" style="color: #374151; padding: 1rem 1.5rem; width: 220px;">Nivel Académico</th>
                            <th class="fw-semibold text-center" style="color: #374151; padding: 1rem 1.5rem; width: 150px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($grados as $grado)
                            <tr style="border-bottom: 1px solid #f3f4f6;">
                                <td class="align-middle" style="color: #6b7280; padding: 1rem 1.5rem; font-weight: 500;">
                                    {{ $grado->id }}
                                </td>
                                <td class="align-middle" style="color: #000000; padding: 1rem 1.5rem; font-weight: 600;">
                                    {{ $grado->nombre }}
                                </td>
                                <td class="align-middle" style="padding: 1rem 1.5rem;">
                                    <div class="d-inline-flex align-items-center" style="background-color: #f0f9ff; color: #0369a1; padding: 0.4rem 0.85rem; border-radius: 6px; font-weight: 500; font-size: 0.875rem; border: 1px solid #bae6fd;">
                                        <svg width="14" height="14" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                                            <path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811V2.828zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492V2.687zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783z"/>
                                        </svg>
                                        {{ $grado->nivel->nombre }}
                                    </div>
                                </td>
                                <td class="align-middle text-center" style="padding: 1rem 1.5rem;">
                                    <form action="{{ route('grados.destroy', $grado) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button 
                                            type="submit"
                                            class="btn btn-sm fw-medium btn-delete d-inline-flex align-items-center"
                                            style="background-color: #dc2626; color: #ffffff; border: none; padding: 0.425rem 1rem; font-size: 0.875rem;"
                                            onclick="return confirm('¿Está seguro de eliminar este grado?')">
                                            <svg width="14" height="14" fill="currentColor" class="me-1" viewBox="0 0 16 16">
                                                <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                                                <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                                            </svg>
                                            Eliminar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center" style="padding: 3.5rem; color: #9ca3af;">
                                    <svg width="56" height="56" fill="currentColor" class="mb-3 mx-auto d-block opacity-50" viewBox="0 0 16 16">
                                        <path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811V2.828zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492V2.687zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783z"/>
                                    </svg>
                                    <p class="mb-1 fw-semibold" style="font-size: 1.05rem; color: #6b7280;">No hay grados registrados</p>
                                    <small class="text-muted">Complete el formulario superior para crear su primer grado académico</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
/* Estilos mejorados */
.form-control:focus,
.form-select:focus {
    border-color: #1e40af !important;
    box-shadow: 0 0 0 0.2rem rgba(30, 64, 175, 0.15) !important;
}

.btn:hover {
    transform: translateY(-1px);
    transition: all 0.2s ease;
}

.btn:active {
    transform: translateY(0);
}

.table-hover tbody tr:hover {
    background-color: #fafbfc;
}

.shadow-sm {
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06) !important;
}

.btn-delete:hover {
    background-color: #b91c1c !important;
}

.btn-primary-custom:hover {
    background-color: #1e3a8a !important;
}

.form-select {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23374151' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    background-size: 16px 12px;
}
</style>
@endsection