<?php

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/RoleMiddleware.php';
require_once __DIR__ . '/../models/SolicitudModel.php';
require_once __DIR__ . '/../models/MovimientoModel.php';

/**
 * NotificacionController.php
 * Controlador ligero para el servicio de notificaciones y alertas en tiempo real.
 * Proporciona un endpoint JSON optimizado para polling en segundo plano.
 */
class NotificacionController
{
    /**
     * Consulta el número actual de novedades (solicitudes pendientes y despachos en espera)
     * según el rol del usuario autenticado.
     * Retorna estrictamente JSON.
     */
    public function consultarNovedades(): void
    {
        // Forzar cabeceras de respuesta JSON y evitar almacenamiento en caché
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Verificar autenticación sin redirección HTTP
        if (!AuthMiddleware::estaAutenticado()) {
            http_response_code(401);
            echo json_encode([
                'status'               => 'error',
                'mensaje'              => 'Sesión no iniciada o expirada',
                'pendientes'           => 0,
                'despachos_pendientes' => 0,
                'timestamp'            => time()
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        try {
            $db = Database::getConnection();
            $solicitudModel  = new SolicitudModel($db);
            $movimientoModel = new MovimientoModel($db);

            $idRol  = (int)AuthMiddleware::idRol();
            $idArea = (int)AuthMiddleware::idArea();

            $pendientes          = 0;
            $despachosPendientes = 0;

            // 1. Solicitudes Pendientes de Aprobación
            // Para Roles Administrador (1) y Jefe de Área (4)
            if (in_array($idRol, [
                RoleMiddleware::ROL_ADMINISTRADOR,
                RoleMiddleware::ROL_JEFE_AREA
            ], true)) {
                $filtroArea = ($idRol === RoleMiddleware::ROL_JEFE_AREA) ? $idArea : null;
                $pendientes = $solicitudModel->contarPendientes($filtroArea);
            }

            // 2. Solicitudes Autorizadas listas para Despacho en Caseta
            // Para Rol Caseta / Encargado Vehicular (2) y Administrador (1)
            if (in_array($idRol, [
                RoleMiddleware::ROL_ADMINISTRADOR,
                RoleMiddleware::ROL_ENCARGADO_VEHICULAR
            ], true)) {
                $despachosPendientes = $movimientoModel->contarSolicitudesListasSalida();
            }

            echo json_encode([
                'status'               => 'success',
                'pendientes'           => $pendientes,
                'despachos_pendientes' => $despachosPendientes,
                'timestamp'            => time()
            ], JSON_UNESCAPED_UNICODE);
            exit();

        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'status'               => 'error',
                'mensaje'              => 'Error al consultar novedades: ' . $e->getMessage(),
                'pendientes'           => 0,
                'despachos_pendientes' => 0,
                'timestamp'            => time()
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }
    }
}
