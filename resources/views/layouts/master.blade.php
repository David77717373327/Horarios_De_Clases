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
    <link rel="stylesheet" href="{{ asset('css/niveles.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @yield('styles')
</head>

<body>

    <!-- Header Principal -->
    <header class="main-header">
        <div class="header-container">

            <!-- Sección Izquierda: Solo botón hamburguesa -->
            <div class="header-left">
                <button class="sidebar-toggle-btn" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="header-divider-vertical"></div>
            </div>

            <!-- Navegación Central -->
            <nav class="header-navigation">
                <ul class="nav-menu"></ul>
            </nav>

            <!-- Controles de Usuario -->
            <div class="user-controls">
                <div class="control-item dropdown">
                    <button class="control-btn user-btn" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="user-avatar-wrapper">
                            <i class="fas fa-user-circle user-icon"></i>
                            <span class="user-status-dot"></span>
                        </div>
                        <div class="user-info">
                            <span class="user-name">@auth {{ Auth::user()->name }} @endauth</span>
                            <span class="user-role">Administrador</span>
                        </div>
                        <i class="fas fa-chevron-down user-chevron"></i>
                    </button>

                    <!-- Dropdown -->
                    <div class="dropdown-menu user-dropdown dropdown-menu-end">

                        <!-- Cabecera -->
                        <div class="user-dropdown-header">
                            <div class="user-dropdown-icon">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <div class="user-dropdown-info">
                                <h6 class="user-dropdown-name">@auth {{ Auth::user()->name }} @endauth</h6>
                                <span class="user-dropdown-role">Administrador</span>
                                <span class="user-dropdown-email">admin@colegio.com</span>
                            </div>
                        </div>

                        <div class="user-dropdown-divider"></div>

                        <div class="user-dropdown-footer">
                            <a href="#" class="user-dropdown-logout"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Cerrar Sesión</span>
                            </a>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </header>

    <!-- Sidebar -->
    <aside class="sidebar">

        <!-- Logo y Brand DENTRO del sidebar -->
        <div class="sidebar-brand">
            <div class="logo-container">
                <img src="{{ asset('images/Logo.png') }}" alt="Logo" class="header-logo">
                <div class="brand-text">
                    <h2 class="brand-title">Gimnasio Humanístico</h2>
                    
                </div>
            </div>
            <div class="sidebar-brand-divider"></div>
        </div>

        <div class="sidebar-body">
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
                        <li class="nav-item">
                            <a href="{{ route('niveles.index') }}" class="nav-link">
                                <i class="fas fa-layer-group nav-icon"></i>
                                <span class="nav-text">Niveles</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('grados.index') }}" class="nav-link">
                                <i class="fas fa-school nav-icon"></i>
                                <span class="nav-text">Grados</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('asignaturas.index') }}" class="nav-link">
                                <i class="fas fa-list-alt nav-icon"></i>
                                <span class="nav-text">Asignaturas</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('profesores.index') }}" class="nav-link">
                                <i class="fas fa-chalkboard-teacher nav-icon"></i>
                                <span class="nav-text">Profesores</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('asignaciones.index') }}">
                                <i class="fas fa-clipboard-list nav-icon"></i>
                                <span class="nav-text">Asignaciones</span>
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

                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('restricciones.index') }}">
                                <i class="fas fa-ban nav-icon"></i>
                                <span class="nav-text">Restricciones</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>

        <!-- Cerrar Sesión fijo abajo -->
        <div class="sidebar-footer">
            <div class="sidebar-footer-divider"></div>
            <a href="#" class="nav-link sidebar-logout"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fas fa-sign-out-alt nav-icon"></i>
                <span class="nav-text">Cerrar Sesión</span>
            </a>
        </div>

    </aside>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    @yield('scripts')

    <!-- Main Content -->
    <main class="main-content">
        @yield('content')
    </main>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>

    <script>
        function toggleSidebar() {
            document.body.classList.toggle('sidebar-collapsed');
            localStorage.setItem(
                'sidebarCollapsed',
                document.body.classList.contains('sidebar-collapsed')
            );
        }

        function toggleDropdown(element) {
            element.classList.toggle('open');
            const submenu = element.nextElementSibling;
            if (submenu) submenu.classList.toggle('show');
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (localStorage.getItem('sidebarCollapsed') === 'true') {
                document.body.classList.add('sidebar-collapsed');
            }

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