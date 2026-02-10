<!DOCTYPE html>
<html lang="es" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión Escolar</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="{{ asset('css/master.css') }}">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Sección para estilos adicionales de cada vista -->
    @yield('styles')
</head>

<body>
    <!-- Header Principal -->
    <header class="main-header">
        <div class="header-container">
            <!-- Sección Izquierda -->
            <div class="header-left">
                <button class="sidebar-toggle-btn" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="logo-container">
                    <img src="{{ asset('images/Logo.png') }}" alt="Logo" class="header-logo">
                    <div class="brand-text">
                        <h2 class="brand-title">Gimnasio Humanístico</h2>
                        <span class="brand-subtitle">Gestión Educativa Nieva Huila</span>
                    </div>
                </div>
            </div>

            <!-- Navegación Central -->
            <nav class="header-navigation">
                <ul class="nav-menu">

                </ul>
            </nav>

            <!-- Controles de Usuario -->
            <div class="user-controls">
                <!-- Perfil de Usuario -->
                <div class="control-item dropdown">
                    <button class="control-btn user-btn" data-bs-toggle="dropdown">
                        <img src="{{ asset('images/Usuario.png') }}" class="user-avatar" alt="Usuario">
                        <div class="user-info">
                            <span class="user-name">@auth {{ Auth::user()->name }} @endauth
                            </span>
                            <span class="user-role">Administrador</span>
                        </div>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="dropdown-menu user-dropdown">
                        <div class="dropdown-header">
                            <div class="user-card">
                                <img src="{{ asset('images/Usuario.png') }}" class="user-card-avatar" alt="Usuario">
                                <div>
                                    <h6>@auth {{ Auth::user()->name }} @endauth
                                    </h6>
                                    <p>admin@colegio.com</p>
                                </div>
                            </div>
                        </div>
                        <div class="dropdown-body">
                            <a href="#" class="dropdown-item">
                                <i class="fas fa-user"></i>Mi Perfil
                            </a>
                            <a href="#" class="dropdown-item">
                                <i class="fas fa-cog"></i>Configuración
                            </a>
                            <a href="#" class="dropdown-item">
                                <i class="fas fa-question-circle"></i>Ayuda
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item logout-item"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt"></i>Cerrar Sesión
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Sidebar -->
    <aside class="sidebar">


        <div class="sidebar-header">
            <div class="sidebar-brand">
                <i class="fa-solid fa-gear"></i>
                <div class="sidebar-brand-text">
                    <h5>Panel Admin</h5>
                    <p>Gestión Escolar</p>
                </div>
            </div>
        </div>




        <div class="sidebar-body">


            <!-- Navegación Sidebar -->
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <h6 class="nav-section-title">Principal</h6>



                    <ul class="nav-list">
                        <li class="nav-item">
                            <a href="{{ route('inicio') }}" class="nav-link active">
                                <i class="fas fa-home nav-icon"></i>
                                <span class="nav-text">Inicio</span>
                            </a>
                        </li>
                    </ul>




                </div>
                <div class="nav-section">
                    <h6 class="nav-section-title">Académico</h6>
                    <ul class="nav-list">
                        <!--Niveles-->
                        <li class="nav-item">
                            <a href="{{ route('niveles.index') }}" class="nav-link">
                                <i class="fas fa-layer-group nav-icon"></i>
                                <span class="nav-text">Niveles</span>
                            </a>
                        </li>

                        <!-- Grados-->
                        <li class="nav-item">
                            <a href="{{ route('grados.index') }}" class="nav-link">
                                <i class="fas fa-school nav-icon"></i>
                                <span class="nav-text">Grados</span>
                            </a>
                        </li>

                        <!-- Asignaturas-->
                        <li class="nav-item">
                            <a href="{{ route('asignaturas.index') }}" class="nav-link">
                                <i class="fas fa-list-alt nav-icon"></i>
                                <span class="nav-text">Asignaturas</span>
                            </a>
                        </li>

                        <!-- Profesores -->
                        <li class="nav-item">
                            <a href="{{ route('profesores.index') }}" class="nav-link">
                                <i class="fas fa-chalkboard-teacher nav-icon"></i>
                                <span class="nav-text">Profesores</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('asignaciones.index') }}">
                                <i class="fas fa-clipboard-list"></i>
                                <span class="nav-text">Asignaciones </span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('restricciones.index') }}">
                                <i class="fas fa-ban"></i>
                                <span class="nav-text">Restricciones </span>
                            </a>
                        </li>












                        <li class="nav-item nav-item-dropdown">
                            <button class="nav-dropdown-toggle" onclick="toggleDropdown(this)">
                                <i class="fas fa-calendar-alt nav-icon"></i>
                                <span class="nav-text">Horarios</span>
                                <i class="fas fa-chevron-right dropdown-arrow"></i>
                            </button>
                            <ul class="nav-submenu">
                                <div>
                                    <li>
                                        <a href="{{ route('horarios.index') }}" class="nav-link">
                                            <i class="fas fa-calendar-plus nav-icon"></i>
                                            <span class="nav-text">Crear Horarios</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('horarios.listar') }}" class="nav-link">
                                            <i class="fas fa-list nav-icon"></i>
                                            <span class="nav-text">Lista de Horarios</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('horarios-profesor.index') }}" class="nav-link">
                                            <i class="fas fa-chalkboard-teacher nav-icon"></i>
                                            <span class="nav-text">Horarios de Profesores</span>
                                        </a>
                                    </li>
                                </div>
                            </ul>
                        </li>


                    </ul>
                </div>
            </nav>
        </div>
    </aside>


    <!-- jQuery (DEBE IR PRIMERO) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    @yield('scripts')
    <!-- Main Content -->
    <main class="main-content">
        @yield('content')
    </main>


    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <form id="logout-form" action="#" method="POST" style="display: none;">
        @csrf
    </form>

    <!-- JavaScript -->
    <script>
        // ⭐ SOLUCIÓN: Agregar/quitar clase cuando se hace hover sobre el sidebar colapsado
        const sidebar = document.querySelector('.sidebar');

        if (sidebar && window.innerWidth > 992) {
            sidebar.addEventListener('mouseenter', function() {
                // Solo agregar la clase si el sidebar está colapsado
                if (document.body.classList.contains('sidebar-collapsed')) {
                    document.body.classList.add('sidebar-hover-expanded');
                }
            });

            sidebar.addEventListener('mouseleave', function() {
                // Quitar la clase cuando el cursor sale del sidebar
                document.body.classList.remove('sidebar-hover-expanded');
            });
        }
        // Toggle sidebar (desktop)
        function toggleSidebar() {
            document.body.classList.toggle('sidebar-collapsed');
            localStorage.setItem(
                'sidebarCollapsed',
                document.body.classList.contains('sidebar-collapsed')
            );
        }

        // Toggle dropdown menú
        function toggleDropdown(element) {
            element.classList.toggle('open');
            const submenu = element.nextElementSibling;
            if (submenu) {
                submenu.classList.toggle('show');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {

            // Cargar preferencia del sidebar
            if (localStorage.getItem('sidebarCollapsed') === 'true') {
                document.body.classList.add('sidebar-collapsed');
            }

            // Responsive: toggle sidebar en mobile
            const sidebarToggle = document.querySelector('.sidebar-toggle-btn');
            if (sidebarToggle && window.innerWidth <= 992) {
                sidebarToggle.addEventListener('click', function() {
                    document.body.classList.toggle('sidebar-open');
                });
            }
        });
    </script>
    @stack('scripts')
</body>

</html>
