<?php

namespace App\Providers;

use App\Interfaces\Repositories\GradoRepositoryInterface;
use Illuminate\Support\ServiceProvider;
use App\Interfaces\Repositories\NivelRepositoryInterface;
use App\Interfaces\Services\GradoServiceInterface;
use App\Interfaces\Services\NivelServiceInterface;
use App\Repositories\GradoRepository;
use App\Repositories\NivelRepository;
use App\Services\GradoService;
use App\Services\NivelService;
use App\Interfaces\Repositories\AsignaturaRepositoryInterface;
use App\Interfaces\Services\AsignaturaServiceInterface;
use App\Repositories\AsignaturaRepository;
use App\Services\AsignaturaService;
use App\Interfaces\Repositories\ProfesorRepositoryInterface;
use App\Interfaces\Services\ProfesorServiceInterface;
use App\Repositories\ProfesorRepository;
use App\Services\ProfesorService;
use App\Interfaces\Repositories\AsignacionAcademicaRepositoryInterface;
use App\Interfaces\Services\AsignacionAcademicaServiceInterface;
use App\Repositories\AsignacionAcademicaRepository;
use App\Services\AsignacionAcademicaService;
use App\Interfaces\Repositories\RestriccionProfesorRepositoryInterface;
use App\Interfaces\Services\RestriccionProfesorServiceInterface;
use App\Repositories\RestriccionProfesorRepository;
use App\Services\RestriccionProfesorService;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Nivel
        $this->app->bind(NivelRepositoryInterface::class, NivelRepository::class);
        $this->app->bind(NivelServiceInterface::class, NivelService::class);

        // Grado
        $this->app->bind(GradoRepositoryInterface::class, GradoRepository::class);
        $this->app->bind(GradoServiceInterface::class, GradoService::class);

        // Asignatura
        $this->app->bind(AsignaturaRepositoryInterface::class, AsignaturaRepository::class);
        $this->app->bind(AsignaturaServiceInterface::class, AsignaturaService::class);

        // Profesor
        $this->app->bind(ProfesorRepositoryInterface::class, ProfesorRepository::class);
        $this->app->bind(ProfesorServiceInterface::class, ProfesorService::class);

        // Asignacion Academica
        $this->app->bind(AsignacionAcademicaRepositoryInterface::class, AsignacionAcademicaRepository::class);
        $this->app->bind(AsignacionAcademicaServiceInterface::class, AsignacionAcademicaService::class);

        // Restriccion Profesor
        $this->app->bind(RestriccionProfesorRepositoryInterface::class, RestriccionProfesorRepository::class);
        $this->app->bind(RestriccionProfesorServiceInterface::class, RestriccionProfesorService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
