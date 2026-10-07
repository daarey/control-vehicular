<?php

// 1. Forzar la zona horaria oficial en el punto de entrada de PHP
date_default_timezone_set('America/Mexico_City');

// 2. Hardening de sesión: configurar ANTES de iniciar la sesión
ini_set('session.cookie_httponly', 1);    // Impedir acceso a PHPSESSID desde JavaScript
ini_set('session.cookie_samesite', 'Lax'); // Prevenir ataques de navegación entre sitios
ini_set('session.use_only_cookies', 1);    // Forzar uso exclusivo de cookies para sesiones

/**
 * public/index.php
 * Front Controller central del Sistema de Control Vehicular.
 * 
 * Único punto de entrada público que enruta las solicitudes hacia
 * sus respectivos controladores de forma segura.
 */

// Autocarga de controladores y middlewares requeridos
require_once __DIR__ . '/../app/middleware/AuthMiddleware.php';
require_once __DIR__ . '/../app/middleware/RoleMiddleware.php';
require_once __DIR__ . '/../app/middleware/CsrfMiddleware.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/DashboardController.php';
require_once __DIR__ . '/../app/controllers/VehiculoController.php';
require_once __DIR__ . '/../app/controllers/SolicitudController.php';
require_once __DIR__ . '/../app/controllers/MovimientoController.php';
require_once __DIR__ . '/../app/controllers/ConductorController.php';
require_once __DIR__ . '/../app/controllers/AreaController.php';
require_once __DIR__ . '/../app/controllers/ReporteController.php';
require_once __DIR__ . '/../app/controllers/UsuarioController.php';
require_once __DIR__ . '/../app/controllers/NotificacionController.php';
require_once __DIR__ . '/../app/controllers/ArchivoController.php';

// Iniciar sesión global
AuthMiddleware::iniciarSesion();

// 3. Protección Anti-CSRF: Validar token obligatoriamente en toda petición POST
//    (excepto login, que aún no tiene sesión con token previo)
$accionPreCsrf = $_GET['action'] ?? null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accionPreCsrf !== 'login') {
    CsrfMiddleware::verificarPost();
}

// Obtener la acción solicitada por URL
$accion = $_GET['action'] ?? null;

// Guard de Seguridad: Bloquear acceso a rutas administrativas para el rol Solicitante
if (AuthMiddleware::estaAutenticado() && RoleMiddleware::esSolicitante()) {
    $rutasRestringidas = [
        'usuarios', 'usuario_crear', 'usuario_editar', 'usuario_eliminar',
        'vehiculos', 'vehiculo_crear', 'vehiculo_editar', 'vehiculo_eliminar',
        'conductores', 'conductor_crear', 'conductor_editar', 'conductor_eliminar',
        'areas', 'area_crear', 'area_editar', 'area_eliminar',
        'reportes', 'reportes_exportar_pdf', 'reportes_exportar_excel',
        'caseta', 'movimientos', 'movimiento_despachar', 'movimiento_retorno',
        'movimientos_exportar_pdf', 'movimientos_exportar_excel',
        'solicitudes_evaluar', 'solicitudes_aprobar', 'solicitudes_rechazar'
    ];

    if (in_array($accion, $rutasRestringidas, true)) {
        $_SESSION['alerta'] = [
            'tipo' => 'warning',
            'mensaje' => 'Acceso denegado: Su perfil de Solicitante está restringido a sus solicitudes de comisión.'
        ];
        header('Location: index.php?action=dashboard');
        exit();
    }
}

// Guard de Seguridad: Restringir rol Encargado de Control Vehicular exclusivamente al módulo de Control de Caseta
if (AuthMiddleware::estaAutenticado() && RoleMiddleware::esEncargadoVehicular()) {
    $rutasPermitidasEncargado = [
        'dashboard',
        'caseta',
        'movimientos', // alias de compatibilidad
        'movimiento_despachar',
        'movimiento_retorno',
        'movimientos_exportar_pdf',
        'movimientos_exportar_excel',
        'notificaciones_novedades',
        'ver_archivo',
        'logout'
    ];

    if ($accion !== null && !in_array($accion, $rutasPermitidasEncargado, true)) {
        $_SESSION['alerta'] = [
            'tipo' => 'warning',
            'mensaje' => 'Acceso denegado: Su perfil de Encargado de Control Vehicular solo tiene acceso al módulo de Control de Caseta.'
        ];
        header('Location: index.php?action=caseta');
        exit();
    }
}

// Si no se especifica acción, determinar según el estado de autenticación
if ($accion === null) {
    $accion = AuthMiddleware::estaAutenticado() ? 'dashboard' : 'login';
}

// Despachador oficial consolidado de rutas
switch ($accion) {
    // ── Autenticación y Sesión ────────────────────────────
    case 'login':
        (new AuthController())->manejarLogin();
        break;

    case 'logout':
        (new AuthController())->cerrarSesion();
        break;

    // ── Panel Principal ───────────────────────────────────
    case 'dashboard':
        (new DashboardController())->index();
        break;

    // ── Solicitudes de Vehículos ──────────────────────────
    case 'solicitudes':
        (new SolicitudController())->index();
        break;

    case 'solicitudes_crear':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new SolicitudController())->store();
        } else {
            (new SolicitudController())->index();
        }
        break;

    case 'solicitudes_evaluar':
        (new SolicitudController())->evaluar();
        break;

    case 'solicitudes_aprobar':
        (new SolicitudController())->aprobar();
        break;

    case 'solicitudes_rechazar':
        (new SolicitudController())->rechazar();
        break;

    case 'solicitudes_cancelar':
        (new SolicitudController())->cancelar();
        break;

    // ── Caseta / Movimientos Vehiculares ──────────────────
    case 'caseta':
    case 'movimientos':
        (new MovimientoController())->index();
        break;

    case 'movimiento_despachar':
        (new MovimientoController())->salida();
        break;

    case 'movimiento_retorno':
        (new MovimientoController())->entrada();
        break;

    case 'movimientos_exportar_pdf':
        (new MovimientoController())->exportarPdf();
        break;

    case 'movimientos_exportar_excel':
        (new MovimientoController())->exportarExcel();
        break;

    // ── Catálogo de Vehículos ─────────────────────────────
    case 'vehiculos':
        (new VehiculoController())->index();
        break;

    case 'vehiculo_crear':
        (new VehiculoController())->store();
        break;

    case 'vehiculo_editar':
        (new VehiculoController())->editar();
        break;

    case 'vehiculo_eliminar':
        (new VehiculoController())->eliminar();
        break;

    // ── Administración de Usuarios ────────────────────────
    case 'usuarios':
        (new UsuarioController())->index();
        break;

    case 'usuario_crear':
        (new UsuarioController())->store();
        break;

    case 'usuario_editar':
        (new UsuarioController())->editar();
        break;

    case 'usuario_eliminar':
        (new UsuarioController())->eliminar();
        break;

    // ── Padrón de Conductores ─────────────────────────────
    case 'conductores':
        (new ConductorController())->index();
        break;

    case 'conductor_crear':
        (new ConductorController())->store();
        break;

    case 'conductor_editar':
        (new ConductorController())->editar();
        break;

    case 'conductor_eliminar':
        (new ConductorController())->eliminar();
        break;

    // ── Reportes y Bitácora ───────────────────────────────
    case 'reportes':
        (new ReporteController())->index();
        break;

    case 'reportes_exportar_pdf':
        (new ReporteController())->exportarPdf();
        break;

    case 'reportes_exportar_excel':
        (new ReporteController())->exportarExcel();
        break;

    // ── Catálogo de Áreas Institucionales ─────────────────
    case 'areas':
        (new AreaController())->index();
        break;

    case 'area_crear':
        (new AreaController())->store();
        break;

    case 'area_editar':
        (new AreaController())->editar();
        break;

    case 'area_eliminar':
        (new AreaController())->eliminar();
        break;

    // ── Archivos Protegidos y Notificaciones en Tiempo Real
    case 'ver_archivo':
        (new ArchivoController())->servir();
        break;

    case 'notificaciones_novedades':
        (new NotificacionController())->consultarNovedades();
        break;

    default:
        // Ruta no encontrada o no válida
        if (AuthMiddleware::estaAutenticado()) {
            header('Location: index.php?action=dashboard');
        } else {
            header('Location: index.php?action=login');
        }
        exit();
}
