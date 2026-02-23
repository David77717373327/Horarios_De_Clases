@extends('layouts.master')

@section('title', 'Inicio')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/welcome.css') }}">
@endsection

@section('content')

{{-- =============================================
     HERO — niños.png en el lado derecho
     ============================================= --}}
<section class="w-hero">

    {{-- Texto --}}
    <div class="w-hero-text">

        <div class="w-chip">
            <i class="fas fa-circle"></i>
            Sistema de Gestión Académica
        </div>

        <h1 class="w-hero-title">
            <span class="line-1">Bienvenido al sistema de</span>
            <span class="line-2">Horarios</span>
            <span class="line-3">de Clases</span>
        </h1>

        <div class="w-hero-eyeline">
            <span class="w-hero-eyeline-bar"></span>
            <span class="w-hero-eyeline-text">Gestión académica inteligente</span>
        </div>

        <p class="w-hero-desc">
            Crea niveles, grados, profesores y asignaturas.
            El sistema genera automáticamente los horarios
            respetando restricciones y preferencias horarias.
        </p>

        <div class="w-hero-btns">
            <a href="#pasos" class="btn-dark">
                <i class="fas fa-arrow-down"></i>
                Ver cómo funciona
            </a>
            <a href="#funciones" class="btn-ghost">
                Funciones <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <div class="w-stats">
            <div>
                <div class="w-stat-n">7<span>+</span></div>
                <div class="w-stat-l">Pasos guiados</div>
            </div>
            <div>
                <div class="w-stat-n">100<span>%</span></div>
                <div class="w-stat-l">Automático</div>
            </div>
            <div>
                <div class="w-stat-n">PDF<span>.</span></div>
                <div class="w-stat-l">Exportable</div>
            </div>
        </div>

    </div>

    {{-- IMAGEN 1: niños.png --}}
    <div class="w-hero-img-wrap">
        <img src="{{ asset('images/niños.png') }}"
             alt="Estudiantes"
             class="w-hero-img">
        <div class="w-hero-shadow"></div>
    </div>

</section>

{{-- =============================================
     STRIP NEGRO
     ============================================= --}}
<div class="w-strip">
    <p class="w-strip-msg">
        <strong>¿Cómo empezar?</strong> — Sigue el orden de los pasos y el sistema hace el resto.
    </p>
    <div class="w-strip-tags">
        <span class="w-strip-tag"><i class="fas fa-layer-group"></i> Niveles y Grados</span>
        <span class="w-strip-tag"><i class="fas fa-book-open"></i> Asignaturas</span>
        <span class="w-strip-tag"><i class="fas fa-chalkboard-teacher"></i> Profesores</span>
        <span class="w-strip-tag"><i class="fas fa-magic"></i> Horario automático</span>
    </div>
</div>

{{-- =============================================
     PASOS — IMAGEN 2: correr.png a la izquierda
     ============================================= --}}
<section class="w-steps" id="pasos">
    <div class="w-steps-inner">

        <div class="w-sec-label">Guía paso a paso</div>
        <h2 class="w-sec-title">¿Cómo usar el sistema?</h2>
        <p class="w-sec-sub">
            Sigue este orden para configurar y generar correctamente los horarios.
            Cada paso depende del anterior.
        </p>

        <div class="w-steps-layout">

            {{-- IMAGEN 2: correr.png — fija al scroll --}}
            <div class="w-steps-img-col">
                <div class="w-steps-img-bg">
                    <img src="{{ asset('images/sele.png') }}"
                         alt="Pasos del sistema"
                         class="w-steps-img">
                </div>
                <p class="w-steps-img-label">
                    <i class="fas fa-route"></i>&nbsp; Sigue el flujo correcto
                </p>
            </div>

            {{-- Grid de pasos --}}
            <div class="w-cards-grid">

                <div class="w-card" data-step="1">
                    <div class="w-card-no">PASO 01</div>
                    <div class="w-card-ico"><i class="fas fa-layer-group"></i></div>
                    <h3 class="w-card-title">Crear el Nivel Académico</h3>
                    <p class="w-card-text">Define la estructura base: Primaria, Secundaria o Media. Agrupa todos los grados que crearás a continuación.</p>
                </div>

                <div class="w-card" data-step="2">
                    <div class="w-card-no">PASO 02</div>
                    <div class="w-card-ico"><i class="fas fa-users"></i></div>
                    <h3 class="w-card-title">Crear Grados y Grupos</h3>
                    <p class="w-card-text">Crea los grados (Primero, Tercero…) y asócialos a su nivel. Ej: 1° Grado → Primaria.</p>
                </div>

                <div class="w-card" data-step="3">
                    <div class="w-card-no">PASO 03</div>
                    <div class="w-card-ico"><i class="fas fa-book-open"></i></div>
                    <h3 class="w-card-title">Crear las Asignaturas</h3>
                    <p class="w-card-text">Registra todas las materias antes de crear profesores. Se asignarán a cada docente en el siguiente paso.</p>
                </div>

                <div class="w-card" data-step="4">
                    <div class="w-card-no">PASO 04</div>
                    <div class="w-card-ico"><i class="fas fa-chalkboard-teacher"></i></div>
                    <h3 class="w-card-title">Registrar Profesores</h3>
                    <p class="w-card-text">Crea cada profesor y asígnale su asignatura. Las materias deben existir previamente.</p>
                </div>

                <div class="w-card" data-step="5">
                    <div class="w-card-no">PASO 05</div>
                    <div class="w-card-ico"><i class="fas fa-clock"></i></div>
                    <h3 class="w-card-title">Asignar Horas al Profesor</h3>
                    <p class="w-card-text">Indica horas, grado y asignatura. Puedes agregar <strong>preferencias</strong>: ej. Matemáticas solo martes y miércoles a 1ª hora.</p>
                </div>

                <div class="w-card" data-step="6">
                    <div class="w-card-no">PASO 06</div>
                    <div class="w-card-ico"><i class="fas fa-ban"></i></div>
                    <h3 class="w-card-title">Agregar Restricciones</h3>
                    <p class="w-card-text">Bloquea filas o columnas específicas de la semana para un docente. El sistema las respetará.</p>
                </div>

                <div class="w-card" data-step="7" style="grid-column:1/-1">
                    <div class="w-card-no">PASO 07</div>
                    <div class="w-card-ico"><i class="fas fa-magic"></i></div>
                    <h3 class="w-card-title">Generar el Horario</h3>
                    <p class="w-card-text">El sistema construye automáticamente el horario del nivel con su grado, organizando materias y docentes por día y hora de forma óptima.</p>
                </div>

            </div>
        </div>
    </div>
</section>

{{-- =============================================
     FUNCIONES — IMAGEN 3: saludos.png a la derecha
     ============================================= --}}
<section class="w-features" id="funciones">
    <div class="w-features-inner">

        <div class="w-sec-label">Funcionalidades adicionales</div>
        <h2 class="w-sec-title">Otras herramientas del sistema</h2>
        <p class="w-sec-sub">
            Exporta, personaliza y gestiona los horarios con estas funciones.
        </p>

        <div class="w-feat-layout">

            {{-- Cards izquierda --}}
            <div>
                <div class="w-feat-grid">

                    <div class="w-feat-card" data-feature="1">
                        <div class="w-feat-ico"><i class="fas fa-file-pdf"></i></div>
                        <h4 class="w-feat-title">Exportar a PDF</h4>
                        <p class="w-feat-text">Genera un PDF del horario con nombre del profesor y sus materias por grado.</p>
                    </div>

                    <div class="w-feat-card" data-feature="2">
                        <div class="w-feat-ico"><i class="fas fa-list-alt"></i></div>
                        <h4 class="w-feat-title">Solo Materias</h4>
                        <p class="w-feat-text">Exporta el horario mostrando únicamente las materias, sin mostrar al docente.</p>
                    </div>

                    <div class="w-feat-card" data-feature="3">
                        <div class="w-feat-ico"><i class="fas fa-user-tie"></i></div>
                        <h4 class="w-feat-title">Materia + Profesor</h4>
                        <p class="w-feat-text">Vista completa con la materia y el nombre del docente en cada celda del horario.</p>
                    </div>

                    <div class="w-feat-card" data-feature="4">
                        <div class="w-feat-ico"><i class="fas fa-sliders-h"></i></div>
                        <h4 class="w-feat-title">Preferencias</h4>
                        <p class="w-feat-text">Define días y horas preferidas. El sistema lo toma como guía al generar el horario.</p>
                    </div>

                </div>
            </div>

            {{-- IMAGEN 3: saludos.png --}}
            <div class="w-feat-img-col">
                <div class="w-feat-img-bg">
                    <img src="{{ asset('images/saludos.png') }}"
                         alt="Funciones del sistema"
                         class="w-feat-img">
                </div>
                <p class="w-feat-img-label">
                    <i class="fas fa-star"></i>&nbsp; Más herramientas disponibles
                </p>
            </div>

        </div>
    </div>
</section>

{{-- FOOTER --}}
<footer class="w-footer">
    <span class="w-footer-copy">© {{ date('Y') }} Horarios de Clases — Todos los derechos reservados</span>
    <span class="w-footer-brand">
        <i class="fas fa-calendar-alt"></i>
        Horarios de Clases
    </span>
</footer>

@endsection

@section('scripts')
<script src="{{ asset('js/welcome.js') }}"></script>
@endsection