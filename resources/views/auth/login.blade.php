<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Colegio Gimnasio Humanístico del Alto Magdalena</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.32/sweetalert2.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.32/sweetalert2.min.css">
</head>
<body>
    <div class="login-container">
        <!-- Tu imagen de fondo original conservada -->
        <div class="login-background">
            <div class="login-image-container">
                <img class="login-bg-image" src="{{ asset('images/iniciooo2.jpeg') }}" alt="Background">
            </div>
        </div>
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-12 col-sm-10 col-md-10 col-lg-8 col-xl-8">
                    <div class="card d-flex mx-auto my-5">
                        <div class="row">
                            <!-- Panel izquierdo - SIN imagen con marca de agua -->
                            <div class="col-md-5 col-sm-12 col-xs-12 c1 p-0">
                                <div class="welcome-section">
                                    <!-- Logo principal - RESTAURANDO TU IMAGEN ORIGINAL -->
                                    <div class="logo-container">
                                        <div id="hero" class="hero-img">
                                            <img class="img-fluid animated" src="{{ asset('images/Logo_inicio.png') }}" alt="Logo GHM">
                                        </div>
                                    </div>
                                    
                                    <!-- Contenido de bienvenida conservado -->
                                    <div class="welcome-content">
                                        <h1 class="welcome-title">Bienvenido(a)</h1>
                                        <p class="welcome-subtitle">Sistema de Gestión Académica</p>
                                        <div class="welcome-indicators">
                                            <span class="indicator"></span>
                                            <span class="indicator"></span>
                                            <span class="indicator"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Panel derecho - Formulario con mejoras sutiles -->
                            <div class="col-md-7 col-sm-12 col-xs-12 c2">
                                <!-- Header mejorado conservando tu estilo -->
                                <header class="form-header">
                                    <div class="institution-identity">
                                        <img src="{{ asset('images/Logo.png') }}" alt="Logo institucional" class="institution-logo">
                                        <div class="institution-details">
                                            <h2 class="institution-name">Gimnasio Humanístico</h2>
                                            <p class="institution-location">
                                                <i class="fas fa-map-marker-alt"></i>
                                                Neiva, Huila
                                            </p>
                                        </div>
                                    </div>
                                </header>

                                <!-- Formulario con mejoras conservando tu diseño -->
                                <div class="login-form-container">
                                    <div class="login-header">
                                        <h3 class="login-title">Iniciar Sesión</h3>
                                        <p class="login-subtitle">Ingresa tus credenciales para acceder al sistema</p>
                                    </div>
                                    
                                    <form method="POST" action="{{ route('login') }}">
                                        @csrf
                                        
                                        <!-- Campo de email conservado -->
                                        <div class="form-group">
                                            <label for="email" class="form-label">Correo Electrónico</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-envelope"></i>
                                                </span>
                                                <input id="email" 
                                                       type="text" 
                                                       class="form-control input-field @error('email') is-invalid @enderror" 
                                                       name="email" 
                                                       value="{{ old('email') }}" 
                                                       placeholder="ejemplo@correo.com" 
                                                       required 
                                                       autofocus>
                                            </div>
                                            @error('email')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <!-- Campo de contraseña conservado -->
                                        <div class="form-group">
                                            <label for="password" class="form-label">Contraseña</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-lock"></i>
                                                </span>
                                                <input id="password" 
                                                       type="password" 
                                                       class="form-control input-field @error('password') is-invalid @enderror" 
                                                       name="password" 
                                                       placeholder="Ingresa tu contraseña" 
                                                       required>
                                                <button class="btn btn-outline-secondary password-toggle" 
                                                        type="button" 
                                                        id="togglePassword">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                            @error('password')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <!-- Botones con jerarquía mejorada -->
                                        <div class="form-actions">
                                            <button type="submit" class="btn btn-primary btn-login">
                                                <i class="fas fa-sign-in-alt"></i>
                                                Iniciar Sesión
                                            </button>
                                            
                                            <a href="{{ route('register') }}" class="btn btn-outline-primary btn-register">
                                                <i class="fas fa-user-plus"></i>
                                                Crear Cuenta
                                            </a>
                                        </div>

                                        <!-- Enlaces adicionales conservados -->
                                        <div class="additional-links">
                                            <a class="forgot-password-link" href="{{ route('password.request') }}">
                                                <i class="fas fa-key"></i>
                                                ¿Olvidaste tu contraseña?
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts conservados -->
    <script>
        // Toggle para mostrar/ocultar contraseña
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>

    <!-- Scripts para SweetAlert2 conservados -->
    @if (session('status'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    icon: 'success',
                    title: 'Usuario Creado',
                    text: 'Esperando verificación del administrador. {{ session('status') }}',
                    confirmButtonText: 'Entendido',
                    timer: 5000,
                    timerProgressBar: true,
                    customClass: {
                        popup: 'swal-custom',
                        title: 'swal-title',
                        content: 'swal-text',
                        confirmButton: 'swal-button'
                    },
                    backdrop: true,
                    allowOutsideClick: false
                });
            });
        </script>
    @endif
    
    @if (session('auth_error'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    icon: 'warning',
                    title: 'Acceso Denegado',
                    text: '{{ session('auth_error') }}',
                    confirmButtonText: 'Intentar de nuevo',
                    timer: 4000,
                    timerProgressBar: true,
                    customClass: {
                        popup: 'swal-custom',
                        title: 'swal-title',
                        content: 'swal-text',
                        confirmButton: 'swal-button'
                    },
                    backdrop: true,
                    allowOutsideClick: false
                });
            });
        </script>
    @endif
</body>
</html>