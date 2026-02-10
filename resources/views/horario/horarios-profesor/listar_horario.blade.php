@extends('layouts.master')

@section('content')
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="header-final-prof">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h1 class="titulo-final-prof">
                                <i class="bi bi-person-workspace me-2"></i>
                                Horarios por Profesor
                            </h1>
                            <p class="subtitulo-final-prof">
                                <i class="bi bi-info-circle me-1"></i>
                                Consulta y visualiza los horarios de cada docente
                            </p>
                        </div>
                        <div>
                            <a href="{{ route('asignaciones.index') }}" class="btn-final-prof btn-gris-prof">
                                <i class="bi bi-arrow-left me-2"></i>Volver
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="card-final-prof shadow-sm mb-4">
            <div class="card-header-final-prof">
                <h6 class="mb-0">
                    <i class="bi bi-funnel-fill me-2"></i>Filtros de Búsqueda
                </h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label class="label-final-prof">
                            <i class="bi bi-person-fill me-1"></i>Profesor
                        </label>
                        <select class="select-final-prof" id="filterProfesor">
                            <option value="">-- Seleccionar profesor --</option>
                            @foreach ($profesores as $profesor)
                                <option value="{{ $profesor->id }}">{{ $profesor->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="label-final-prof">
                            <i class="bi bi-calendar3 me-1"></i>Año Lectivo
                        </label>
                        <select class="select-final-prof" id="filterYear">
                            @foreach ($years as $yr)
                                <option value="{{ $yr }}" {{ $yr == date('Y') ? 'selected' : '' }}>
                                    {{ $yr }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <button type="button" class="btn-final-prof btn-azul-prof w-100" id="btnBuscar">
                            <i class="bi bi-search me-2"></i>Buscar Horario
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenedor de Horario -->
        <div id="horarioContainer">
            <div class="estado-vacio-final-prof">
                <i class="bi bi-calendar-week icono-vacio-final-prof"></i>
                <h5 class="titulo-vacio-final-prof">Selecciona un profesor y año</h5>
                <p class="texto-vacio-final-prof">El horario del docente aparecerá organizado por días y horas</p>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <link href="{{ asset('css/horario-profesor.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/horario-profesor.js') }}"></script>
@endsection
