<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Gimnasio Humanístico del Alto Magdalena</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.32/sweetalert2.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.32/sweetalert2.min.css">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>

<div class="login-container">

    <div class="login-background"></div>

    <div class="main-wrapper">
        <div class="login-card">

            {{-- ==============================
                 PANEL IZQUIERDO — CARRUSEL 3D
            =============================== --}}
            <div class="panel-left">

                <div class="carousel-3d">

                    <div class="slide-3d active" data-index="0">
                        <div class="slide-scene">
                            <img src="{{ asset('images/saluditos.png') }}" alt="Escena 1" class="img-3d">
                        </div>
                    </div>

                    <div class="slide-3d" data-index="1">
                        <div class="slide-scene">
                            <img src="{{ asset('images/juego.png') }}" alt="Escena 2" class="img-3d">
                        </div>
                    </div>

                    <div class="slide-3d" data-index="2">
                        <div class="slide-scene">
                            <img src="{{ asset('images/abrazo.png') }}" alt="Escena 3" class="img-3d">
                        </div>
                    </div>

                </div>

                <div class="welcome-content">
                    <h1 class="welcome-title">Panel de<br>Administración</h1>
                    <p class="welcome-subtitle">Sistema de Gestión Académica</p>
                    <div class="carousel-dots" id="carouselDots">
                        <button class="dot active" data-target="0" aria-label="Slide 1"></button>
                        <button class="dot"        data-target="1" aria-label="Slide 2"></button>
                        <button class="dot"        data-target="2" aria-label="Slide 3"></button>
                    </div>
                </div>

            </div>

            {{-- ============================
                 PANEL DERECHO — FORMULARIO
            ============================= --}}
            <div class="panel-right">

                <div class="panel-right-inner">

                    {{-- Header institucional --}}
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

                    {{-- Título del formulario --}}
                    <div class="login-header">
                        <h3 class="login-title">Acceso Administrativo</h3>
                        <p class="login-subtitle">Ingresa tus credenciales para continuar</p>
                    </div>

                    {{-- Formulario --}}
                    <form method="POST" action="{{ route('login') }}" id="loginForm" novalidate>
                        @csrf

                        {{-- Correo --}}
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

                        {{-- Contraseña --}}
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
                                <button class="password-toggle"
                                        type="button"
                                        id="togglePassword"
                                        aria-label="Mostrar u ocultar contraseña">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        {{-- CAPTCHA --}}
                        <div class="captcha-group">
                            <div class="captcha-box"
                                 id="captchaBox"
                                 role="button"
                                 tabindex="0"
                                 aria-label="Verificar que no eres un robot">
                                <div class="captcha-left">
                                    <div class="captcha-checkbox" id="captchaCheckbox">
                                        <i class="fas fa-check" id="captchaCheckIcon"></i>
                                    </div>
                                    <span class="captcha-label" id="captchaLabel">No soy un robot</span>
                                </div>
                                <div class="captcha-right">
                                    <div class="captcha-logo">
                                        <i class="fas fa-shield-alt"></i>
                                    </div>
                                    <span class="captcha-brand">reCAPTCHA<br>Privacidad · Condiciones</span>
                                </div>
                            </div>
                            <input type="hidden" name="captcha_verified" id="captchaVerified" value="0">
                        </div>

                        {{-- Botón --}}
                        <div class="form-actions">
                            <button type="submit" class="btn-login" id="submitBtn">
                                <i class="fas fa-right-to-bracket"></i>
                                Iniciar Sesión
                            </button>
                        </div>

                        {{-- ¿Olvidaste tu contraseña? --}}
                        <div class="additional-links">
                            <a class="forgot-password-link" href="{{ route('password.request') }}">
                                <i class="fas fa-key"></i>
                                ¿Olvidaste tu contraseña?
                            </a>
                        </div>

                    </form>

                </div>{{-- /panel-right-inner --}}

            </div>{{-- /panel-right --}}

        </div>{{-- /login-card --}}
    </div>{{-- /main-wrapper --}}

</div>{{-- /login-container --}}

<script src="{{ asset('js/login.js') }}"></script>

@if (session('status'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: '{{ session('status') }}',
                confirmButtonText: 'Entendido',
                timer: 5000,
                timerProgressBar: true,
                customClass: { popup: 'swal-custom', title: 'swal-title', confirmButton: 'swal-button' },
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
                customClass: { popup: 'swal-custom', title: 'swal-title', confirmButton: 'swal-button' },
                backdrop: true,
                allowOutsideClick: false
            });
        });
    </script>
@endif

</body>
</html>