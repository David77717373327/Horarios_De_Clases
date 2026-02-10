<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Asignatura;
use App\Models\Grado;
use App\Models\Nivel;
use App\Models\Horario;
use App\Models\AsignacionAcademica;
use App\Models\RestriccionProfesor;

class WelcomeController extends Controller
{
    /**
     * Muestra el dashboard de bienvenida con el progreso del sistema
     */
    public function index()
    {
        // ============================================
        // PASO 1: NIVELES EDUCATIVOS
        // ============================================
        $niveles = Nivel::all();
        $totalNiveles = $niveles->count();
        $paso1Completo = $totalNiveles > 0;

        // ============================================
        // PASO 2: GRADOS
        // ============================================
        $grados = Grado::with('nivel')->get();
        $totalGrados = $grados->count();
        $paso2Completo = $totalGrados > 0 && $paso1Completo;

        // ============================================
        // PASO 3: ASIGNATURAS
        // ============================================
        $asignaturas = Asignatura::all();
        $totalAsignaturas = $asignaturas->count();
        $paso3Completo = $totalAsignaturas > 0;

        // ============================================
        // PASO 4: PROFESORES
        // ============================================
        $profesores = User::where('role', 'professor')->get();
        $totalProfesores = $profesores->count();
        $paso4Completo = $totalProfesores > 0;

        // ============================================
        // PASO 5: ASIGNACIONES ACADÉMICAS
        // ============================================
        $asignaciones = AsignacionAcademica::with(['profesor', 'grado', 'asignatura'])->get();
        $totalAsignaciones = $asignaciones->count();
        $paso5Completo = $totalAsignaciones > 0 && $paso4Completo && $paso3Completo && $paso2Completo;

        // ============================================
        // PASO 6: RESTRICCIONES (OPCIONAL)
        // ============================================
        $restricciones = RestriccionProfesor::where('activa', true)->get();
        $totalRestricciones = $restricciones->count();

        // ============================================
        // PASO 7: HORARIOS GENERADOS
        // ============================================
        $gradosConHorario = Horario::select('grado_id')->distinct()->get();
        $totalGradosConHorario = $gradosConHorario->count();
        $paso7Completo = $totalGradosConHorario > 0 && $paso5Completo;

        // ============================================
        // CALCULAR PROGRESO GENERAL DEL SISTEMA
        // ============================================
        $pasosTotales = 7;
        $pasosCompletados = 0;
        
        if ($paso1Completo) $pasosCompletados++;
        if ($paso2Completo) $pasosCompletados++;
        if ($paso3Completo) $pasosCompletados++;
        if ($paso4Completo) $pasosCompletados++;
        if ($paso5Completo) $pasosCompletados++;
        if ($totalRestricciones > 0) $pasosCompletados++;
        if ($paso7Completo) $pasosCompletados++;

        $progresoGeneral = round(($pasosCompletados / $pasosTotales) * 100);

        // ============================================
        // DETERMINAR SIGUIENTE PASO RECOMENDADO
        // ============================================
        $siguientePaso = $this->determinarSiguientePaso(
            $paso1Completo,
            $paso2Completo,
            $paso3Completo,
            $paso4Completo,
            $paso5Completo,
            $paso7Completo
        );

        // ============================================
        // ESTADÍSTICAS ADICIONALES
        // ============================================
        $estadisticas = [
            'profesoresConAsignaciones' => AsignacionAcademica::select('profesor_id')->distinct()->count(),
            'gradosSinHorario' => $totalGrados - $totalGradosConHorario,
            'profesoresSinAsignaciones' => $totalProfesores - AsignacionAcademica::select('profesor_id')->distinct()->count(),
            'asignaturasActivas' => $totalAsignaturas,
        ];

        // ============================================
        // RETORNAR VISTA CON TODOS LOS DATOS
        // ============================================
        return view('welcome', compact(
            'progresoGeneral',
            'siguientePaso',
            'niveles',
            'totalNiveles',
            'paso1Completo',
            'grados',
            'totalGrados',
            'paso2Completo',
            'asignaturas',
            'totalAsignaturas',
            'paso3Completo',
            'profesores',
            'totalProfesores',
            'paso4Completo',
            'asignaciones',
            'totalAsignaciones',
            'paso5Completo',
            'restricciones',
            'totalRestricciones',
            'totalGradosConHorario',
            'paso7Completo',
            'estadisticas'
        ));
    }

    /**
     * Determina cuál es el siguiente paso que el usuario debe completar
     */
    private function determinarSiguientePaso($paso1, $paso2, $paso3, $paso4, $paso5, $paso7)
    {
        if (!$paso1) {
            return [
                'numero' => 1,
                'titulo' => 'Crear Niveles Educativos',
                'descripcion' => 'Comience definiendo los niveles educativos (Primaria, Secundaria, etc.)',
                'ruta' => 'niveles.index',
                'icono' => 'layers'
            ];
        }

        if (!$paso2) {
            return [
                'numero' => 2,
                'titulo' => 'Crear Grados',
                'descripcion' => 'Defina los grados para cada nivel educativo',
                'ruta' => 'grados.index',
                'icono' => 'grid'
            ];
        }

        if (!$paso3) {
            return [
                'numero' => 3,
                'titulo' => 'Registrar Asignaturas',
                'descripcion' => 'Agregue las asignaturas que se impartirán',
                'ruta' => 'asignaturas.index',
                'icono' => 'book'
            ];
        }

        if (!$paso4) {
            return [
                'numero' => 4,
                'titulo' => 'Registrar Profesores',
                'descripcion' => 'Agregue los profesores al sistema',
                'ruta' => 'profesores.index',
                'icono' => 'users'
            ];
        }

        if (!$paso5) {
            return [
                'numero' => 5,
                'titulo' => 'Crear Asignaciones Académicas',
                'descripcion' => 'Asigne profesores a grados y asignaturas',
                'ruta' => 'asignaciones.index',
                'icono' => 'user-check'
            ];
        }

        if (!$paso7) {
            return [
                'numero' => 7,
                'titulo' => 'Generar Horarios',
                'descripcion' => 'Ya puede crear los horarios académicos',
                'ruta' => 'horarios.index',
                'icono' => 'calendar'
            ];
        }

        return [
            'numero' => 8,
            'titulo' => '¡Sistema Completo!',
            'descripcion' => 'Todos los pasos están completos. Puede gestionar sus horarios.',
            'ruta' => 'horarios.listar',
            'icono' => 'check-circle'
        ];
    }
}