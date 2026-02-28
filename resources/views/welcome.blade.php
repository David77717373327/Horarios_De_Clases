@extends('layouts.master')

@section('title', 'Horarios Académicos - Inicio')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/welcome.css?v=13.0') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
@endsection

@section('content')

<div class="ws">

  {{-- ══ SIDEBAR ══ --}}
  <aside class="ws-sidebar">
    <div class="sb-header">
      <div class="sb-title">
        <i class="fas fa-rocket"></i>
        Configuración Rápida
      </div>
      <div class="sb-progress">
        <div class="progress-bar">
          <div class="progress-fill" style="width:0%"></div>
        </div>
        <span class="progress-text">0/6 completado</span>
      </div>
    </div>

    <div class="sb-steps">
      <a href="{{ route('niveles.index') }}" class="sb-step sb-step-1" data-step="1">
        <div class="sb-step-num">1</div>
        <div class="sb-step-text">
          <span class="step-title">Nivel Académico</span>
          <span class="step-desc">Primaria, Secundaria, Media</span>
        </div>
        <i class="fas fa-chevron-right step-arrow"></i>
      </a>
      <a href="{{ route('grados.index') }}" class="sb-step sb-step-2" data-step="2">
        <div class="sb-step-num">2</div>
        <div class="sb-step-text">
          <span class="step-title">Grados y Grupos</span>
          <span class="step-desc">Asignar a niveles</span>
        </div>
        <i class="fas fa-chevron-right step-arrow"></i>
      </a>
      <a href="{{ route('asignaturas.index') }}" class="sb-step sb-step-3" data-step="3">
        <div class="sb-step-num">3</div>
        <div class="sb-step-text">
          <span class="step-title">Asignaturas</span>
          <span class="step-desc">Crear materias</span>
        </div>
        <i class="fas fa-chevron-right step-arrow"></i>
      </a>
      <a href="{{ route('profesores.index') }}" class="sb-step sb-step-4" data-step="4">
        <div class="sb-step-num">4</div>
        <div class="sb-step-text">
          <span class="step-title">Profesores</span>
          <span class="step-desc">Asignar materias</span>
        </div>
        <i class="fas fa-chevron-right step-arrow"></i>
      </a>
      <a href="{{ route('restricciones.index') }}" class="sb-step sb-step-5" data-step="5">
        <div class="sb-step-num">5</div>
        <div class="sb-step-text">
          <span class="step-title">Restricciones</span>
          <span class="step-desc">Bloquear horarios</span>
        </div>
        <i class="fas fa-chevron-right step-arrow"></i>
      </a>
      <a href="{{ route('horarios.index') }}" class="sb-step sb-step-primary sb-step-6" data-step="6">
        <div class="sb-step-num primary">6</div>
        <div class="sb-step-text">
          <span class="step-title primary">Generar Horario</span>
          <span class="step-desc primary">Automático e inteligente</span>
        </div>
        <i class="fas fa-chevron-right step-arrow"></i>
      </a>
    </div>

    <div class="sb-footer">
      <div class="stat-group">
        <div class="stat"><span class="stat-number">6</span><span class="stat-label">Pasos</span></div>
        <div class="stat-divider"></div>
        <div class="stat"><span class="stat-number green">0</span><span class="stat-label">Conflictos</span></div>
        <div class="stat-divider"></div>
        <div class="stat"><span class="stat-number">100%</span><span class="stat-label">Automático</span></div>
      </div>
      <button class="btn-export" onclick="window.location='{{ route('horarios.listar') }}'">
        <i class="fas fa-file-pdf"></i> Exportar PDF
      </button>
    </div>
  </aside>

  {{-- ══ MAIN ══ --}}
  <main class="ws-main">
    <div class="hero-section">

      {{-- ═══════════════════════════════════════════════
           PANEL IZQUIERDO
           Layout:
           ┌─────────────────────────────────────┐
           │  [badge]                            │
           │  TÍTULO GRANDE                      │
           │  subtítulo                          │
           │  [botones]                          │
           │  ✓ features                         │
           │                                     │
           │  ┌──────┐  ┌──────┐  ┌──────┐      │
           │  │sele  │  │niños │  │salud.│      │
           │  └──────┘  └──────┘  └──────┘      │
           └─────────────────────────────────────┘
           La img "sele" señala hacia el texto (arriba-izq)
           Las imgs "niños" y "saludos" saludan al centro
      ════════════════════════════════════════════════ --}}
      <div class="hero-left">

        {{-- Texto --}}
        <div class="hero-content">
          <div class="hero-badge">
            <span class="badge-icon"></span>
            Sistema Inteligente
          </div>

          <h1 class="hero-title">
            Horarios<br>
            perfectos<br>
            <span class="gradient-text">sin conflictos</span>
          </h1>

          <p class="hero-subtitle">
            Configura en 6 pasos simples. El algoritmo genera horarios óptimos
            respetando restricciones y preferencias automáticamente.
          </p>

          <div class="hero-actions">
            <a href="{{ route('niveles.index') }}" class="btn-primary large">
              <i class="fas fa-play"></i>
              Comenzar Configuración
            </a>
            <a href="{{ route('horarios.index') }}" class="btn-secondary">
              <i class="fas fa-eye"></i>
              Ver Horarios
            </a>
          </div>

          <div class="hero-features">
            <div class="feature-item">
              <span class="feature-check"><i class="fas fa-check"></i></span>
              Algoritmo IA optimizado
            </div>
            <div class="feature-item">
              <span class="feature-check"><i class="fas fa-check"></i></span>
              100% sin conflictos
            </div>
            <div class="feature-item">
              <span class="feature-check"><i class="fas fa-check"></i></span>
              Exporta PDF/Excel
            </div>
          </div>
        </div>

        {{-- 
          IMÁGENES integradas — absolutes en la parte inferior-derecha del panel
          sele.png    → extremo izquierdo, señala hacia el título (arriba-derecha)
          niños.png   → centro, grupos sentados saludando
          saludos.png → derecha, saludando hacia el centro
          
          El panel izquierdo tiene overflow:hidden para que las imgs
          queden recortadas limpiamente en el borde inferior
        --}}
        <div class="hero-chars">
          {{-- niño que señala hacia el texto --}}
          <img src="{{ asset('images/sele.png') }}"
               alt="Señalando el sistema"
               class="hc-img hc-pointer">

          {{-- grupo de niños en el centro --}}
          <img src="{{ asset('images/niños.png') }}"
               alt="Estudiantes"
               class="hc-img hc-group">

          {{-- niños saludando a la derecha --}}
          <img src="{{ asset('images/saludos.png') }}"
               alt="Saludando"
               class="hc-img hc-wave">
        </div>

      </div>{{-- /hero-left --}}

      {{-- ═══ PANEL DERECHO: card --}}
      <div class="hero-right">
        <div class="card-preview">
          <div class="card-header">
            <div class="status-indicator active">
              <span class="status-dot"></span>
              Listo para generar
            </div>
            <div class="card-title">Sistema Activo</div>
            <div class="card-subtitle">Preparado • 100% automático</div>
          </div>
          <div class="card-stats">
            <div class="stat-card">
              <span class="stat-value" data-count="6">6</span>
              <span class="stat-unit">Pasos</span>
            </div>
            <div class="stat-card">
              <span class="stat-value zero" data-count="0">0</span>
              <span class="stat-unit">Conflictos</span>
            </div>
            <div class="stat-card">
              <span class="stat-value percent" data-pct="100">100%</span>
              <span class="stat-unit">Precisión</span>
            </div>
          </div>
          <div class="card-elements">
            <span class="card-elements-label">Elementos del Sistema</span>
            <div class="card-element-row">
              <div class="card-element-name"><span>📚</span> Materias</div>
              <span class="card-element-badge" id="count-materias">--</span>
            </div>
            <div class="card-element-row">
              <div class="card-element-name"><span>👨‍🏫</span> Docentes</div>
              <span class="card-element-badge" id="count-docentes">--</span>
            </div>
            <div class="card-element-row">
              <div class="card-element-name"><span>🎒</span> Grupos</div>
              <span class="card-element-badge" id="count-grupos">--</span>
            </div>
          </div>
        </div>
      </div>

    </div>{{-- /hero-section --}}

    {{-- FEATURES BAR --}}
    <div class="features-bar">
      <div class="feature-item"><i class="fas fa-book"></i> Materias</div>
      <div class="feature-divider"></div>
      <div class="feature-item"><i class="fas fa-user-tie"></i> Docentes</div>
      <div class="feature-divider"></div>
      <div class="feature-item"><i class="fas fa-sliders-h"></i> Preferencias</div>
      <div class="feature-divider"></div>
      <div class="feature-item"><i class="fas fa-ban"></i> Restricciones</div>
      <div class="feature-divider"></div>
      <a href="{{ route('horarios.index') }}" class="feature-cta">
        <i class="fas fa-magic"></i> Generar Ahora
      </a>
    </div>

  </main>
</div>

@endsection
@section('scripts')
<script src="{{ asset('js/welcome.js?v=13.0') }}"></script>
@endsection