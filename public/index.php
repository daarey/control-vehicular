<?php

// 1. Forzar la zona horaria oficial en el punto de entrada de PHP
date_default_timezone_set('America/Mexico_City');

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

// Iniciar sesión global
AuthMiddleware::iniciarSesion();

// Obtener el controlador y la acción solicitados por URL
$controlador = $_GET['controller'] ?? null;
$accion = $_GET['action'] ?? null;

// Guard de Seguridad: Bloquear acceso a rutas de gestión administrativa para el rol Solicitante
if (AuthMiddleware::estaAutenticado() && RoleMiddleware::esSolicitante()) {
    $rutasRestringidas = [
        'usuarios', 'usuario', 'usuarios_store', 'usuario_store', 'usuarios_editar', 'usuario_update', 'usuarios_eliminar', 'usuarios_delete', 'usuario_delete',
        'vehiculos', 'vehiculo', 'vehiculos_store', 'vehiculos_editar', 'vehiculos_eliminar', 'store',
        'reportes', 'reporte', 'reportes_exportar_excel', 'reporte_exportar_excel', 'reportes_exportar_pdf', 'reporte_exportar_pdf',
        'conductores', 'conductor', 'conductores_store', 'conductores_editar', 'conductores_eliminar',
        'areas', 'area', 'areas_store', 'areas_editar', 'areas_eliminar',
        'movimientos', 'movimiento', 'movimientos_store', 'movimientos_cerrar', 'movimientos_exportar_excel', 'movimiento_exportar_excel', 'movimientos_exportar_pdf', 'movimiento_exportar_pdf',
        'solicitudes_evaluar', 'solicitudes_pendientes', 'solicitud_evaluar', 'evaluar', 'solicitudes_aprobar', 'solicitudes_autorizar', 'solicitudes_rechazar'
    ];

    $controladoresRestringidos = [
        'usuario', 'usuarios', 'vehiculo', 'vehiculos', 'reporte', 'reportes',
        'conductor', 'conductores', 'area', 'areas', 'movimiento', 'movimientos'
    ];

    if (in_array($accion, $rutasRestringidas, true) || in_array($controlador, $controladoresRestringidos, true)) {
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
        // Pantalla y vistas de Caseta
        'dashboard', 'caseta', 'dashboard_caseta', 'dashboard/caseta',
        'movimientos', 'movimiento',

        // Procesamiento de salidas (Despachar Salida)
        'movimiento_salida', 'movimientos_salida',
        'movimiento_despachar', 'movimientos_despachar',
        'movimiento_guardar_salida', 'movimientos_guardar_salida',
        'movimientos_store', 'movimiento_store',
        'salida', 'despachar',

        // Procesamiento de retornos (Registrar Retorno / Entradas)
        'movimiento_entrada', 'movimientos_entrada',
        'movimiento_retorno', 'movimientos_retorno',
        'movimiento_registrar_retorno', 'movimientos_registrar_retorno',
        'movimiento_guardar_retorno', 'movimientos_guardar_retorno',
        'movimientos_cerrar', 'movimiento_cerrar',
        'entrada', 'retorno', 'cerrar',

        // Exportaciones de Caseta
        'movimientos_exportar_excel', 'movimiento_exportar_excel',
        'movimientos_exportar_pdf', 'movimiento_exportar_pdf',

        // Notificaciones en tiempo real y sesión
        'notificaciones', 'notificaciones_novedades', 'logout'
    ];

    $controladoresPermitidosEncargado = [
        null, '', 'movimiento', 'movimientos', 'caseta', 'dashboard', 'notificacion', 'notificaciones'
    ];

    $esAccionPermitida = ($accion === null || in_array($accion, $rutasPermitidasEncargado, true));
    $esControladorPermitido = in_array($controlador, $controladoresPermitidosEncargado, true);

    if (!$esAccionPermitida || !$esControladorPermitido) {
        $_SESSION['alerta'] = [
            'tipo' => 'warning',
            'mensaje' => 'Acceso denegado: Su perfil de Encargado de Control Vehicular solo tiene acceso al módulo de Control de Caseta.'
        ];
        header('Location: index.php?action=movimientos');
        exit();
    }
}

// Soporte para formato ?controller=solicitud&action=...
if ($controlador === 'solicitud' || $controlador === 'solicitudes') {
    $solCtrl = new SolicitudController();
    if ($accion === 'store') {
        $solCtrl->store();
        exit();
    } elseif ($accion === 'cancelar') {
        $solCtrl->cancelar();
        exit();
    } elseif ($accion === 'evaluar' || $accion === 'pendientes') {
        $solCtrl->evaluar();
        exit();
    } elseif ($accion === 'aprobar' || $accion === 'autorizar') {
        $solCtrl->aprobar();
        exit();
    } elseif ($accion === 'rechazar') {
        $solCtrl->rechazar();
        exit();
    } else {
        $solCtrl->index();
        exit();
    }
}

// Soporte para formato ?controller=vehiculo&action=...
if ($controlador === 'vehiculo' || $controlador === 'vehiculos') {
    $vehiculoCtrl = new VehiculoController();
    if ($accion === 'store') {
        $vehiculoCtrl->store();
        exit();
    } elseif ($accion === 'editar') {
        $vehiculoCtrl->editar();
        exit();
    } elseif ($accion === 'eliminar') {
        $vehiculoCtrl->eliminar();
        exit();
    } else {
        $vehiculoCtrl->index();
        exit();
    }
}

// Soporte para formato ?controller=usuario&action=...
if ($controlador === 'usuario' || $controlador === 'usuarios') {
    $usuarioCtrl = new UsuarioController();
    if ($accion === 'store') {
        $usuarioCtrl->store();
        exit();
    } elseif ($accion === 'editar' || $accion === 'update') {
        $usuarioCtrl->update();
        exit();
    } elseif ($accion === 'eliminar' || $accion === 'delete') {
        $usuarioCtrl->delete();
        exit();
    } else {
        $usuarioCtrl->index();
        exit();
    }
}

// Soporte para formato ?controller=area&action=...
if ($controlador === 'area' || $controlador === 'areas') {
    $areaCtrl = new AreaController();
    if ($accion === 'store') {
        $areaCtrl->store();
        exit();
    } elseif ($accion === 'editar' || $accion === 'update') {
        $areaCtrl->editar();
        exit();
    } elseif ($accion === 'eliminar' || $accion === 'delete') {
        $areaCtrl->eliminar();
        exit();
    } else {
        $areaCtrl->index();
        exit();
    }
}

// Soporte para formato ?controller=conductor&action=...
if ($controlador === 'conductor' || $controlador === 'conductores') {
    $conductorCtrl = new ConductorController();
    if ($accion === 'store') {
        $conductorCtrl->store();
        exit();
    } elseif ($accion === 'editar' || $accion === 'update') {
        $conductorCtrl->editar();
        exit();
    } elseif ($accion === 'eliminar' || $accion === 'delete') {
        $conductorCtrl->eliminar();
        exit();
    } else {
        $conductorCtrl->index();
        exit();
    }
}

// Soporte para formato ?controller=movimiento&action=...
if ($controlador === 'movimiento' || $controlador === 'movimientos' || $controlador === 'caseta') {
    $movCtrl = new MovimientoController();
    if (in_array($accion, ['store', 'salida', 'registrarSalida', 'despachar', 'movimiento_despachar', 'guardar_salida'], true)) {
        $movCtrl->salida();
        exit();
    } elseif (in_array($accion, ['cerrar', 'entrada', 'registrarEntrada', 'retorno', 'registrarRetorno', 'guardar_retorno'], true)) {
        $movCtrl->entrada();
        exit();
    } elseif ($accion === 'exportarExcel' || $accion === 'exportar_excel') {
        $movCtrl->exportarExcel();
        exit();
    } elseif ($accion === 'exportarPdf' || $accion === 'exportar_pdf') {
        $movCtrl->exportarPdf();
        exit();
    } else {
        $movCtrl->index();
        exit();
    }
}

// Soporte para formato ?controller=reporte&action=...
if ($controlador === 'reporte' || $controlador === 'reportes') {
    $repCtrl = new ReporteController();
    if ($accion === 'exportarExcel' || $accion === 'exportar_excel') {
        $repCtrl->exportarExcel();
        exit();
    } elseif ($accion === 'exportarPdf' || $accion === 'exportar_pdf') {
        $repCtrl->exportarPdf();
        exit();
    } else {
        $repCtrl->index();
        exit();
    }
}

// Soporte para formato ?controller=notificacion&action=...
if ($controlador === 'notificacion' || $controlador === 'notificaciones') {
    (new NotificacionController())->consultarNovedades();
    exit();
}

// Si no se especifica acción, determinar según el estado de autenticación
if ($accion === null) {
    if (AuthMiddleware::estaAutenticado()) {
        $accion = 'dashboard';
    } else {
        $accion = 'login';
    }
}

// Despachador de rutas
switch ($accion) {
    case 'login':
        (new AuthController())->manejarLogin();
        break;

    case 'logout':
        (new AuthController())->cerrarSesion();
        break;

    case 'dashboard':
        (new DashboardController())->index();
        break;

    case 'vehiculos':
        (new VehiculoController())->index();
        break;

    case 'vehiculos_store':
    case 'store':
        (new VehiculoController())->store();
        break;

    case 'vehiculos_editar':
        (new VehiculoController())->editar();
        break;

    case 'vehiculos_eliminar':
        (new VehiculoController())->eliminar();
        break;

    case 'solicitudes':
        (new SolicitudController())->index();
        break;

    case 'solicitudes_crear':
    case 'solicitud_crear':
        (new SolicitudController())->index();
        break;

    case 'solicitudes_store':
    case 'solicitud_store':
        (new SolicitudController())->store();
        break;

    case 'solicitudes_cancelar':
    case 'solicitud_cancelar':
        (new SolicitudController())->cancelar();
        break;

    case 'solicitudes_evaluar':
    case 'solicitudes_pendientes':
    case 'solicitud_evaluar':
    case 'evaluar':
        (new SolicitudController())->evaluar();
        break;

    case 'solicitudes_aprobar':
    case 'solicitud_aprobar':
    case 'aprobar':
    case 'solicitudes_autorizar':
    case 'solicitud_autorizar':
        (new SolicitudController())->aprobar();
        break;

    case 'solicitudes_rechazar':
    case 'solicitud_rechazar':
    case 'rechazar':
        (new SolicitudController())->rechazar();
        break;

    case 'caseta':
    case 'dashboard/caseta':
    case 'dashboard_caseta':
    case 'movimientos':
    case 'movimiento':
        (new MovimientoController())->index();
        break;

    case 'movimientos_store':
    case 'movimiento_store':
        (new MovimientoController())->store();
        break;

    case 'movimiento_salida':
    case 'movimientos_salida':
    case 'movimiento_despachar':
    case 'movimientos_despachar':
    case 'movimiento_guardar_salida':
    case 'movimientos_guardar_salida':
    case 'salida':
    case 'despachar':
        (new MovimientoController())->salida();
        break;

    case 'movimiento_entrada':
    case 'movimientos_entrada':
    case 'movimiento_retorno':
    case 'movimientos_retorno':
    case 'movimiento_registrar_retorno':
    case 'movimientos_registrar_retorno':
    case 'movimiento_guardar_retorno':
    case 'movimientos_guardar_retorno':
    case 'entrada':
    case 'retorno':
        (new MovimientoController())->entrada();
        break;

    case 'movimientos_cerrar':
    case 'movimiento_cerrar':
    case 'cerrar':
        (new MovimientoController())->cerrar();
        break;


    case 'movimientos_exportar_excel':
    case 'movimiento_exportar_excel':
        (new MovimientoController())->exportarExcel();
        break;

    case 'movimientos_exportar_pdf':
    case 'movimiento_exportar_pdf':
        (new MovimientoController())->exportarPdf();
        break;

    case 'conductores':
        (new ConductorController())->index();
        break;

    case 'conductores_store':
        (new ConductorController())->store();
        break;

    case 'conductores_editar':
        (new ConductorController())->editar();
        break;

    case 'conductores_eliminar':
        (new ConductorController())->eliminar();
        break;

    case 'areas':
        (new AreaController())->index();
        break;

    case 'areas_store':
        (new AreaController())->store();
        break;

    case 'areas_editar':
        (new AreaController())->editar();
        break;

    case 'areas_eliminar':
        (new AreaController())->eliminar();
        break;

    case 'reportes':
        (new ReporteController())->index();
        break;

    case 'reportes_exportar_excel':
    case 'reporte_exportar_excel':
        (new ReporteController())->exportarExcel();
        break;

    case 'reportes_exportar_pdf':
    case 'reporte_exportar_pdf':
        (new ReporteController())->exportarPdf();
        break;

    case 'usuarios':
        (new UsuarioController())->index();
        break;

    case 'usuarios_store':
    case 'usuario_store':
        (new UsuarioController())->store();
        break;

    case 'usuarios_editar':
    case 'usuario_update':
        (new UsuarioController())->update();
        break;

    case 'usuarios_eliminar':
    case 'usuarios_delete':
    case 'usuario_delete':
        (new UsuarioController())->delete();
        break;

    case 'notificaciones_novedades':
    case 'notificaciones':
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
