@extends('layouts.master')

@section('title', 'Inicio')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/welcome.css') }}">
@endsection

@section('content')
<div class="dashboard-container">
    
    @php
        // Obtener datos reales de la base de datos
        $currentYear = date('Y');
        
        // Niveles
        $niveles = \App\Models\Nivel::with('grados')->get();
        
        // Grados agrupados por nivel
        $gradosPorNivel = \App\Models\Grado::with('nivel')
            ->orderBy('nivel_id')
            ->orderBy('nombre')
            ->get()
            ->groupBy('nivel_id');
        
        // Asignaturas (primeras 3 para el dropdown)
        $asignaturas = \App\Models\Asignatura::with('profesores')
            ->orderBy('nombre')
            ->limit(10)
            ->get();
        
        // Profesores
        $profesores = \App\Models\User::where('role', 'professor')
            ->orderBy('name')
            ->limit(4)
            ->get();
        
        // Estadísticas de horarios
        $totalHorarios = \App\Models\Horario::where('year', $currentYear)->count();
        $totalAsignaciones = \App\Models\AsignacionAcademica::where('year', $currentYear)->count();
        
        // Asignatura actual (primera con horario)
        $asignaturaActual = \App\Models\Horario::with(['asignatura', 'grado'])
            ->where('year', $currentYear)
            ->whereNotNull('asignatura_id')
            ->first();
    @endphp

    {{-- Grid de Tarjetas Principal (3x2) --}}
    <div class="dashboard-grid">
        
        {{-- Tarjeta 1: Horarios --}}
        <div class="dashboard-card">
            <div class="card-header">
                <h3 class="card-title">Horarios</h3>
            </div>
            <div class="card-content">
                {{-- Mini calendario visual con datos reales --}}
                <div class="mini-schedule">
                    <div class="schedule-days">
                        <span>Lun</span>
                        <span>Mar</span>
                        <span>Mie</span>
                        <span>Jue</span>
                        <span>Vie</span>
                        <span>Sab</span>
                        <span>Dom</span>
                    </div>
                    @php
                        // Obtener distribución de horarios por día
                        $horariosPorDia = \App\Models\Horario::where('year', $currentYear)
                            ->selectRaw('dia_semana, COUNT(*) as total')
                            ->groupBy('dia_semana')
                            ->pluck('total', 'dia_semana');
                        
                        $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
                    @endphp
                    <div class="schedule-blocks">
                        @foreach($dias as $dia)
                            @php
                                $total = $horariosPorDia[$dia] ?? 0;
                                $color = $total > 0 ? ($total > 5 ? 'block-orange' : ($total > 3 ? 'block-green' : 'block-teal')) : 'block-empty';
                            @endphp
                            <div class="block {{ $color }}" title="{{ $dia }}: {{ $total }} clases"></div>
                        @endforeach
                    </div>
                    <div class="schedule-blocks">
                        @for($i = 0; $i < 5; $i++)
                            <div class="block block-empty"></div>
                        @endfor
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('horarios.index') }}" class="card-link">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                    Crear Horarios
                </a>
                <a href="{{ route('horarios.listar') }}" class="card-link">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="8" y1="6" x2="21" y2="6"/>
                        <line x1="8" y1="12" x2="21" y2="12"/>
                        <line x1="8" y1="18" x2="21" y2="18"/>
                        <line x1="3" y1="6" x2="3.01" y2="6"/>
                        <line x1="3" y1="12" x2="3.01" y2="12"/>
                        <line x1="3" y1="18" x2="3.01" y2="18"/>
                    </svg>
                    Lista de Horarios
                </a>
            </div>
        </div>

        {{-- Tarjeta 2: Asignaturas (Primera) --}}
        <div class="dashboard-card">
            <div class="card-header">
                <h3 class="card-title">Asignaturas</h3>
            </div>
            <div class="card-content">
                <div class="subject-list">
                    @if($niveles->first())
                        <div class="subject-item">
                            <span class="subject-badge">{{ $niveles->first()->nombre }}</span>
                            <select class="subject-select" onchange="window.location.href='{{ route('asignaturas.index') }}'">
                                @foreach($asignaturas->take(3) as $asignatura)
                                    <option>{{ $asignatura->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if($asignaturas->first())
                            <div class="subject-item">
                                <span class="subject-text">{{ $asignaturas->first()->nombre }}</span>
                            </div>
                        @endif
                    @else
                        <div class="subject-item">
                            <span class="subject-text">No hay asignaturas registradas</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Tarjeta 3: Asignaturas (Segunda - con detalle de hora) --}}
        <div class="dashboard-card">
            <div class="card-header">
                <h3 class="card-title">Asignaturas</h3>
            </div>
            <div class="card-content">
                <div class="subject-detail">
                    @if($asignaturaActual)
                        <div class="subject-time">
                            {{ \Carbon\Carbon::parse($asignaturaActual->hora_inicio)->format('h:i A') }} - 
                            {{ $asignaturaActual->asignatura->nombre }} 
                            ({{ $asignaturaActual->grado->nombre }})
                        </div>
                    @else
                        <div class="subject-time">No hay horarios programados</div>
                    @endif
                    <select class="subject-select" onchange="window.location.href='{{ route('asignaturas.index') }}'">
                        @forelse($asignaturas->take(3) as $asignatura)
                            <option>{{ $asignatura->nombre }}</option>
                        @empty
                            <option>Sin asignaturas</option>
                        @endforelse
                    </select>
                </div>
            </div>
        </div>

        {{-- Tarjeta 4: Profesores --}}
        <div class="dashboard-card">
            <div class="card-header">
                <h3 class="card-title">Profesores</h3>
            </div>
            <div class="card-content">
                <div class="professor-grid">
                    @php
                        // Obtener estadísticas reales de aulas/recursos
                        $totalGrados = \App\Models\Grado::count();
                        $gradosConHorario = \App\Models\Horario::where('year', $currentYear)
                            ->distinct('grado_id')
                            ->count('grado_id');
                    @endphp
                    
                    <div class="professor-item">
                        <svg class="icon-room" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <line x1="9" y1="3" x2="9" y2="21"/>
                        </svg>
                        <span>{{ $totalGrados }} Grados</span>
                    </div>
                    <div class="professor-item">
                        <svg class="icon-board" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="4" width="20" height="14" rx="2"/>
                            <line x1="8" y1="21" x2="16" y2="21"/>
                            <line x1="12" y1="18" x2="12" y2="21"/>
                        </svg>
                        <span>{{ $gradosConHorario }} con Horario</span>
                    </div>
                    <div class="professor-item">
                        <svg class="icon-users" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                        </svg>
                        <span>{{ $profesores->count() }} Profesores</span>
                    </div>
                    <div class="professor-item">
                        <svg class="icon-classroom" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        </svg>
                        <span>{{ $totalAsignaciones }} Asignaciones</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tarjeta 5: Grados y Niveles --}}
        <div class="dashboard-card">
            <div class="card-header">
                <h3 class="card-title">Grados y Niveles</h3>
            </div>
            <div class="card-content">
                <div class="grades-container">
                    @foreach($niveles->take(2) as $nivel)
                        <div class="grade-column">
                            <div class="grade-header">{{ $nivel->nombre }}</div>
                            <div class="grade-list">
                                @forelse($nivel->grados->take(4) as $grado)
                                    <div class="grade-item">{{ $grado->nombre }}</div>
                                @empty
                                    <div class="grade-item">Sin grados</div>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                    
                    @if($niveles->count() < 2)
                        <div class="grade-column">
                            <div class="grade-header">Sin nivel</div>
                            <div class="grade-list">
                                <div class="grade-item">-</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Tarjeta 6: Restricciones --}}
        <div class="dashboard-card">
            <div class="card-header">
                <h3 class="card-title">Restricciones</h3>
            </div>
            <div class="card-content">
                <div class="restrictions-actions">
                    <a href="{{ route('horarios.index') }}" class="action-btn action-btn-create">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="16"/>
                            <line x1="8" y1="12" x2="16" y2="12"/>
                        </svg>
                        <span>Crear Horarios</span>
                    </a>
                    <a href="{{ route('horarios.listar') }}" class="action-btn action-btn-list">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="8" y1="6" x2="21" y2="6"/>
                            <line x1="8" y1="12" x2="21" y2="12"/>
                            <line x1="8" y1="18" x2="21" y2="18"/>
                            <line x1="3" y1="6" x2="3.01" y2="6"/>
                            <line x1="3" y1="12" x2="3.01" y2="12"/>
                            <line x1="3" y1="18" x2="3.01" y2="18"/>
                        </svg>
                        <span>Lista de Horarios</span>
                    </a>
                </div>
            </div>
        </div>

    </div>

    {{-- Segunda Fila: Asignaturas con búsqueda y Restricciones visuales --}}
    <div class="dashboard-grid-second">
        
        {{-- Panel de Profesores con búsqueda --}}
        <div class="dashboard-card wide-card">
            <div class="card-header">
                <h3 class="card-title">Profesores</h3>
            </div>
            <div class="card-content">
                <div class="search-box">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.35-4.35"/>
                    </svg>
                    <input type="text" placeholder="Search" class="search-input" 
                           onkeyup="filtrarProfesores(this.value)">
                </div>
                <div class="professor-list" id="professor-list">
                    @forelse($profesores as $profesor)
                        <div class="professor-row" data-name="{{ strtolower($profesor->name) }}">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($profesor->name) }}&background=e5e7eb&color=374151" 
                                 alt="{{ $profesor->name }}" class="avatar">
                            <span>{{ $profesor->name }}</span>
                        </div>
                    @empty
                        <div class="professor-row">
                            <span>No hay profesores registrados</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Panel de Restricciones visuales --}}
        <div class="dashboard-card wide-card">
            <div class="card-header">
                <h3 class="card-title">Restricciones</h3>
            </div>
            <div class="card-content">
                @php
                    // Estadísticas reales de restricciones
                    $totalRestricciones = \App\Models\RestriccionProfesor::where('year', $currentYear)
                        ->where('activa', true)
                        ->count();
                    $restriccionesPorDia = \App\Models\RestriccionProfesor::where('year', $currentYear)
                        ->where('activa', true)
                        ->whereNotNull('dia_semana')
                        ->selectRaw('dia_semana, COUNT(*) as total')
                        ->groupBy('dia_semana')
                        ->pluck('total', 'dia_semana');
                    
                    $maxRestricciones = $restriccionesPorDia->max() ?: 1;
                @endphp
                
                <div class="restrictions-visual">
                    <div class="restriction-chart">
                        <div class="chart-label">Restricciones por Día</div>
                        <div class="chart-bars">
                            @php
                                $lunes = $restriccionesPorDia['Lunes'] ?? 0;
                                $martes = $restriccionesPorDia['Martes'] ?? 0;
                                $porcentajeLunes = $maxRestricciones > 0 ? ($lunes / $maxRestricciones) * 100 : 0;
                                $porcentajeMartes = $maxRestricciones > 0 ? ($martes / $maxRestricciones) * 100 : 0;
                            @endphp
                            <div class="bar bar-orange" style="width: {{ max($porcentajeLunes, 10) }}%" 
                                 title="Lunes: {{ $lunes }} restricciones"></div>
                            <div class="bar bar-teal" style="width: {{ max($porcentajeMartes, 10) }}%" 
                                 title="Martes: {{ $martes }} restricciones"></div>
                        </div>
                    </div>
                    <div class="restriction-chart">
                        <div class="chart-label">Ocupación General</div>
                        <div class="chart-bars">
                            @php
                                $totalEspacios = $totalGrados * 50; // 50 horas por grado aprox
                                $porcentajeOcupacion = $totalEspacios > 0 ? min(($totalHorarios / $totalEspacios) * 100, 100) : 0;
                                $porcentajeLibre = 100 - $porcentajeOcupacion;
                            @endphp
                            <div class="bar bar-orange" style="width: {{ max($porcentajeOcupacion, 5) }}%" 
                                 title="Ocupado: {{ number_format($porcentajeOcupacion, 1) }}%"></div>
                            <div class="bar bar-teal" style="width: {{ max($porcentajeLibre, 5) }}%" 
                                 title="Libre: {{ number_format($porcentajeLibre, 1) }}%"></div>
                        </div>
                    </div>
                    <div class="restriction-info">
                        <p>Total de restricciones activas: {{ $totalRestricciones }}</p>
                        <p>Horarios programados: {{ $totalHorarios }}</p>
                        <p>Asignaciones académicas: {{ $totalAsignaciones }}</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

<script>
function filtrarProfesores(termino) {
    const rows = document.querySelectorAll('#professor-list .professor-row');
    const terminoLower = termino.toLowerCase();
    
    rows.forEach(row => {
        const nombre = row.getAttribute('data-name');
        if (nombre && nombre.includes(terminoLower)) {
            row.style.display = 'flex';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>
@endsection