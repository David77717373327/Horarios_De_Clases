<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AsignaturaController;
use App\Http\Controllers\ProfesorController;
use App\Http\Controllers\NivelController;
use App\Http\Controllers\GradoController;
use App\Http\Controllers\HorarioController;
use App\Http\Controllers\HorarioListController;
use App\Http\Controllers\AsignacionAcademicaController;
use App\Http\Controllers\RestriccionProfesorController;
use App\Http\Controllers\GeneradorHorarioController;
use App\Http\Controllers\HorarioProfesorController;





Route::get('/', function () {
    if (auth()->check()) {
        return view('welcome');
    }
    return redirect()->route('login');
})->name('inicio');




// ✅ TODAS las rutas protegidas aquí dentro
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {



// ========================================
// CRUD de Asignaturas
// ========================================
Route::resource('asignaturas', AsignaturaController::class);

// ========================================
// CRUD de Profesores
// ========================================
Route::prefix('profesores')->name('profesores.')->group(function () {
    Route::get('/', [ProfesorController::class, 'index'])->name('index');
    Route::post('/', [ProfesorController::class, 'store'])->name('store');
    Route::get('/{profesor}', [ProfesorController::class, 'show'])->name('show');
    Route::put('/{profesor}', [ProfesorController::class, 'update'])->name('update');
    Route::delete('/{profesor}', [ProfesorController::class, 'destroy'])->name('destroy');
    Route::post('/{profesor}/asignar-asignaturas', [ProfesorController::class, 'asignarAsignaturas'])
        ->name('asignar-asignaturas');
});

// ========================================
// CRUD de Niveles
// ========================================
Route::resource('niveles', NivelController::class)->except(['create', 'edit', 'show']);

// ========================================
// CRUD de Grados
// ========================================
Route::resource('grados', GradoController::class)->except(['create', 'edit', 'show']);

// ========================================
// GESTIÓN DE HORARIOS
// ========================================
Route::prefix('horarios')->group(function () {
    // Vistas principales
    Route::get('/', [HorarioController::class, 'index'])->name('horarios.index');
    Route::get('/listar', [HorarioListController::class, 'index'])->name('horarios.listar');

    // Listar horarios y estadísticas (AJAX)
    Route::get('/listar/obtener', [HorarioListController::class, 'getHorariosByNivel']);
    Route::get('/listar/estadisticas', [HorarioListController::class, 'getEstadisticas']);

    // AJAX auxiliares
    Route::get('/ajax/grados-por-nivel/{nivelId}', [HorarioController::class, 'getGradosByNivel']);
    Route::get('/ajax/profesores-por-asignatura/{asignaturaId}', [HorarioController::class, 'getProfesoresByAsignatura']);
    Route::get('/ajax/asignaturas', [HorarioController::class, 'getAsignaturas']);
    Route::get('/ajax/profesor/{profesorId}', [HorarioController::class, 'getProfesor']);

    // Operaciones del horario
    Route::get('/obtener', [HorarioController::class, 'getHorario']);
    Route::post('/guardar', [HorarioController::class, 'store']);
    Route::post('/validar-conflicto', [HorarioController::class, 'validarConflicto']);
    Route::delete('/eliminar', [HorarioController::class, 'destroy']);

    Route::get('/horarios/pdf', [HorarioListController::class, 'pdf'])
    ->name('horarios.pdf');

    Route::get('/horarios/pdf-solo-materias', [HorarioListController::class, 'pdfSoloMaterias'])
    ->name('horarios.pdf.solo.materias');
});

// ============================================
// RUTAS PARA ASIGNACIONES ACADÉMICAS
// ============================================
Route::prefix('asignaciones')->group(function () {
    Route::get('/', [AsignacionAcademicaController::class, 'index'])->name('asignaciones.index');
    Route::get('/listar', [AsignacionAcademicaController::class, 'listar'])->name('asignaciones.listar');
    Route::post('/', [AsignacionAcademicaController::class, 'store'])->name('asignaciones.store');
    Route::get('/{id}', [AsignacionAcademicaController::class, 'show'])->name('asignaciones.show');
    Route::put('/{id}', [AsignacionAcademicaController::class, 'update'])->name('asignaciones.update');
    Route::delete('/{id}', [AsignacionAcademicaController::class, 'destroy'])->name('asignaciones.destroy');
    
    // Rutas auxiliares existentes
    Route::get('/profesor/{profesorId}/resumen', [AsignacionAcademicaController::class, 'resumenProfesor'])->name('asignaciones.resumen-profesor');
    Route::get('/grado/{gradoId}/resumen', [AsignacionAcademicaController::class, 'resumenGrado'])->name('asignaciones.resumen-grado');
    Route::get('/nivel/{nivelId}/grados', [AsignacionAcademicaController::class, 'gradosPorNivel'])->name('asignaciones.grados-nivel');
    Route::post('/validar', [AsignacionAcademicaController::class, 'validar'])->name('asignaciones.validar');
    
    
    // Nuevas rutas para funcionalidades mejoradas
    Route::get('/matriz/{gradoId}', [AsignacionAcademicaController::class, 'obtenerMatriz'])->name('asignaciones.matriz');
    Route::post('/masiva', [AsignacionAcademicaController::class, 'guardarMasiva'])->name('asignaciones.masiva');
    Route::get('/resumen-year', [AsignacionAcademicaController::class, 'resumenYear'])->name('asignaciones.resumen-year');
    Route::post('/copiar-year', [AsignacionAcademicaController::class, 'copiarYear'])->name('asignaciones.copiar-year');
    Route::get('/estadisticas', [AsignacionAcademicaController::class, 'estadisticas'])->name('asignaciones.estadisticas');
});

// ============================================
// IMPORTANTE: Necesitas también estas rutas
// ============================================
// Profesores - para obtener asignaturas del profesor
Route::get('/profesores/{id}', [ProfesorController::class, 'show'])->name('profesores.show');

// O si no tienes ProfesorController, agrega esta ruta directamente:
Route::get('/profesores/{id}', function($id) {
    $profesor = \App\Models\User::where('role', 'professor')
        ->with('asignaturas')
        ->findOrFail($id);
    return response()->json($profesor);
})->name('profesores.show');

// Restricciones de Profesores
Route::prefix('restricciones')->group(function () {
    Route::get('/', [RestriccionProfesorController::class, 'index'])->name('restricciones.index');
    Route::get('/listar', [RestriccionProfesorController::class, 'listar'])->name('restricciones.listar');
    Route::post('/', [RestriccionProfesorController::class, 'store'])->name('restricciones.store');
    Route::get('/{id}', [RestriccionProfesorController::class, 'show'])->name('restricciones.show');
    Route::put('/{id}', [RestriccionProfesorController::class, 'update'])->name('restricciones.update');
    Route::delete('/{id}', [RestriccionProfesorController::class, 'destroy'])->name('restricciones.destroy');
    Route::post('/{id}/toggle', [RestriccionProfesorController::class, 'toggleActiva'])->name('restricciones.toggle');
    Route::get('/profesor/{profesorId}', [RestriccionProfesorController::class, 'restriccionesProfesor'])->name('restricciones.profesor');
    Route::post('/verificar', [RestriccionProfesorController::class, 'verificarRestriccion'])->name('restricciones.verificar');
});






// ============================================
// 🔥 GENERADOR AUTOMÁTICO DE HORARIOS (REFACTORIZADO)
// ============================================
Route::prefix('generador')->name('generador.')->group(function () {
    // 🆕 RUTAS PARA GENERACIÓN POR NIVEL (SISTEMA v8.0 - REFACTORIZADO)
    Route::post('/nivel/{nivelId}/generar', [GeneradorHorarioController::class, 'generarAutomatico'])
        ->name('generar.nivel');
    
    // Nota: Si necesitas el método estadisticas(), deberás agregarlo al controlador refactorizado
    // Route::get('/nivel/{nivelId}/estadisticas', [GeneradorHorarioController::class, 'estadisticas'])
    //     ->name('estadisticas.nivel');
    
    // Rutas antiguas por grado (mantener por compatibilidad si hay código legacy)
    // Si ya no las necesitas, puedes comentarlas o eliminarlas
    Route::post('/grado/{gradoId}/generar', [GeneradorHorarioController::class, 'generarAutomatico'])
        ->name('generar');
    
    // Route::get('/grado/{gradoId}/estadisticas', [GeneradorHorarioController::class, 'estadisticas'])
    //     ->name('estadisticas');
});













// ============================================
// HORARIOS DE PROFESORES
// ============================================
Route::prefix('horarios-profesor')->group(function () {
    Route::get('/', [HorarioProfesorController::class, 'index'])
        ->name('horarios-profesor.index');
    
    Route::get('/obtener', [HorarioProfesorController::class, 'obtenerHorario'])
        ->name('horarios-profesor.obtener');
    
    Route::get('/pdf', [HorarioProfesorController::class, 'descargarPdf'])
        ->name('horarios-profesor.pdf');
    
    Route::get('/listar', [HorarioProfesorController::class, 'listar'])
        ->name('horarios-profesor.listar');
    
    Route::get('/estadisticas', [HorarioProfesorController::class, 'estadisticas'])
        ->name('horarios-profesor.estadisticas');
    
    Route::get('/descargar-todos', [HorarioProfesorController::class, 'descargarTodosPdf'])
        ->name('horarios-profesor.descargar-todos');
});




});