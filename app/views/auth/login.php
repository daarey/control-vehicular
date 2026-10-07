<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Acceso al sistema institucional de control vehicular. Inicie sesión con sus credenciales.">
    <title>Acceso al Sistema — Control Vehicular | SECOTED</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>

    <div class="split-screen">
        <div class="panel-identity" aria-hidden="true">
            <div class="panel-overlay">

                <div class="panel-decor panel-decor--top"></div>
                <div class="panel-decor panel-decor--bottom"></div>

                <div class="panel-content">

                    <div class="panel-logo-wrap">
                        <img
                            src="assets/img/SECOTED_Logo.webp"
                            alt="Logotipo SECOTED"
                            class="panel-logo"
                        >
                    </div>

                    <div class="panel-text">
                        <p class="panel-label">Gobierno del Estado de Durango</p>
                        <h1 class="panel-title">Sistema de<br>Control Vehicular</h1>
                        <p class="panel-subtitle">Módulo de gestión operativa</p>
                    </div>

                    <div class="panel-footer">
                        <span class="panel-footer-dot"></span>
                        <span class="panel-footer-dot"></span>
                        <span class="panel-footer-dot panel-footer-dot--active"></span>
                    </div>

                </div>
            </div>
        </div>
        <div class="panel-login">

            <div class="login-box">

                <div class="login-header">
                    <div class="login-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                    <h2>Iniciar Sesión</h2>
                    <p>Acceda con sus credenciales institucionales</p>
                </div>

                <?php 
                    $mensajeExito = '';
                    if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'sesion_cerrada') {
                        $mensajeExito = 'Su sesión se ha cerrado correctamente.';
                    }
                    if (empty($error) && isset($_GET['error'])) {
                        if ($_GET['error'] === 'sesion_requerida') {
                            $error = 'Debe iniciar sesión para acceder a esa sección.';
                        } elseif ($_GET['error'] === 'acceso_denegado') {
                            $error = 'Acceso denegado: no cuenta con los permisos necesarios.';
                        }
                    }
                ?>

                <?php if (!empty($mensajeExito)): ?>
                    <div class="login-success" role="status">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        <?php echo htmlspecialchars($mensajeExito, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="login-error" role="alert">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <form action="index.php" method="POST" novalidate>
                    <?php echo class_exists('Csrf') ? Csrf::renderField() : (class_exists('CsrfMiddleware') ? CsrfMiddleware::renderField() : ''); ?>

                    <div class="form-group">
                        <label for="correo">Correo Electrónico</label>
                        <div class="input-wrap">
                            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                            <input
                                type="email"
                                id="correo"
                                name="correo"
                                required
                                placeholder="correo@durango.gob.mx"
                                autocomplete="email"
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="contrasena">Contraseña</label>
                        <div class="input-wrap">
                            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <input
                                type="password"
                                id="contrasena"
                                name="contrasena"
                                required
                                placeholder="••••••••"
                                autocomplete="current-password"
                            >
                        </div>
                    </div>

                    <div class="security-notice">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        <span>Conexión cifrada. Nunca comparta sus credenciales.</span>
                    </div>

                    <button type="submit" id="btn-ingresar" class="btn-primary">
                        Ingresar al Sistema
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </button>

                </form>

                <div class="login-links">
                    <a href="#">¿Olvidó su contraseña?</a>
                    <span class="links-sep">·</span>
                    <span>Solicitar acceso</span>
                </div>

            </div>

        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>
