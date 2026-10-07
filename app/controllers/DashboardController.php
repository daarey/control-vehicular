<?php

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/RoleMiddleware.php';
require_once __DIR__ . '/../models/DashboardModel.php';
require_once __DIR__ . '/../models/UsuarioModel.php';
require_once __DIR__ . '/../models/SolicitudModel.php';
require_once __DIR__ . '/../models/MotivoModel.php';

/**
 * DashboardController.php
 * Controlador ejecutivo del Tablero Principal de Control Vehicular.
 * v3: Enrutamiento por rol con inyección correcta de variables para cada dashboard.
 *   - ROL 1 (Admin) / ROL 4 (Jefe de Área) → dashboard/index.php
 *   - ROL 2 (Encargado Vehicular)           → dashboard/caseta.php  (con datos de MovimientoModel)
 *   - ROL 3 (Solicitante)                   → dashboard/solicitante.php
 */
class DashboardController
{
    private DashboardModel $modeloDashboard;

    public function __construct(?PDO $conexion = null)
    {
        if ($conexion === null) {
            $conexion = Database::getConnection();
        }
        $this->modeloDashboard = new DashboardModel($conexion);
    }

    public function index(): void
    {
        AuthMiddleware::verificarSesion();

        $idRol = (int)AuthMiddleware::idRol();

        // ── Dashboard del Solicitante ─────────────────────────────────
        if ($idRol === RoleMiddleware::ROL_SOLICITANTE) {
            $this->dashboardSolicitante();
            return;
        }

        // ── Dashboard Operativo de Caseta (Encargado Vehicular / ROL 2) ──
        if ($idRol === RoleMiddleware::ROL_ENCARGADO_VEHICULAR) {
            $this->dashboardCaseta();
            return;
        }

        // ── Dashboard Ejecutivo (Admin y Jefe de Área) ────────────────
        $kpis = [
            'total_vehiculos'         => 0,
            'vehiculos_disponibles'   => 0,
            'vehiculos_en_ruta'       => 0,
            'vehiculos_mantenimiento' => 0,
            'solicitudes_pendientes'  => 0,
            'movimientos_hoy'         => 0,
            'retornos_pendientes'     => 0,
            'total_conductores'       => 0,
        ];
        $movimientosActivos = [];
        $chartVehiculos   = ['Disponibles' => 0, 'En ruta' => 0, 'En mantenimiento' => 0];
        $chartSolicitudes = ['Autorizadas' => 0, 'Pendientes' => 0, 'Rechazadas' => 0];

        try {
            $kpis               = $this->modeloDashboard->obtenerKpisGlobales();
            $movimientosActivos = $this->modeloDashboard->obtenerSalidasActivasCaseta();
            $chartVehiculos     = $this->modeloDashboard->obtenerConteoPorEstadoOperativo();
            $chartSolicitudes   = $this->modeloDashboard->obtenerConteoPorEstatusSolicitudes();
        } catch (Throwable $e) {
            error_log('Error en DashboardController::index(): ' . $e->getMessage());
        }

        $tituloPagina = 'Panel de Control Ejecutivo';
        $paginaActual = 'dashboard';

        require_once __DIR__ . '/../views/dashboard/index.php';
    }

    /**
     * Dashboard operativo del Encargado Vehicular (ROL 2 / Caseta).
     * Inyecta todas las variables requeridas por dashboard/caseta.php:
     *   - $solicitudesAutorizadas, $vehiculosEnRuta, $movimientos, $totalVehiculosDisp
     */
    private function dashboardCaseta(): void
    {
        require_once __DIR__ . '/../models/MovimientoModel.php';
        require_once __DIR__ . '/../models/VehiculoModel.php';

        $solicitudesAutorizadas = [];
        $vehiculosEnRuta        = [];
        $movimientos            = [];
        $totalVehiculosDisp     = 0;

        try {
            $db     = Database::getConnection();
            $movMod = new MovimientoModel($db);
            $vehMod = new VehiculoModel($db);

            $solicitudesAutorizadas = $movMod->obtenerSolicitudesListasSalida();
            $vehiculosEnRuta        = $movMod->obtenerVehiculosEnRuta();
            $movimientos            = $movMod->obtenerRecientes(50);
            $totalVehiculosDisp     = count($vehMod->obtenerDisponibles());
        } catch (Throwable $e) {
            error_log('Error en DashboardController::dashboardCaseta(): ' . $e->getMessage());
        }

        $tituloPagina = 'Control de Caseta — Salidas y Retornos';
        // 'movimientos' activa el link correcto en el sidebar para este rol
        $paginaActual = 'movimientos';

        require_once __DIR__ . '/../views/dashboard/caseta.php';
    }

    /**
     * Dashboard personal del Solicitante.
     * Carga: datos de licencia del usuario, solicitud activa hoy y solicitudes recientes.
     */
    private function dashboardSolicitante(): void
    {
        $idUsuario = (int)AuthMiddleware::idUsuario();

        $usuario              = null;
        $licencia             = null;
        $solicitudesRecientes = [];
        $solicitudActiva      = null;
        $motivos              = [];

        try {
            $db = Database::getConnection();

            // Datos del servidor público y su área
            $usuarioModel = new UsuarioModel($db);
            $usuario      = $usuarioModel->obtenerPorId($idUsuario);
            $licencia     = $usuarioModel->obtenerLicencia($idUsuario);

            // Solicitudes del usuario en sesión (las más recientes primero)
            $solicitudModel       = new SolicitudModel($db);
            $solicitudesRecientes = $solicitudModel->obtenerPorUsuario($idUsuario);

            // Catálogo de motivos para el modal de nueva solicitud
            $motivoModel = new MotivoModel($db);
            $motivos     = $motivoModel->obtenerTodos();

            // Detectar solicitud activa/autorizada o en curso con vehículo asignado
            foreach ($solicitudesRecientes as $s) {
                $estado = $s['estado_solicitud'] ?? '';
                if (($estado === 'Autorizada' || $estado === 'En curso') && !empty($s['vehiculo_placas'])) {
                    $solicitudActiva = $s;
                    break;
                }
            }
        } catch (Throwable $e) {
            error_log('Error en DashboardController::dashboardSolicitante(): ' . $e->getMessage());
        }

        $tituloPagina = 'Mi Panel de Comisiones';
        $paginaActual = 'dashboard';

        require_once __DIR__ . '/../views/dashboard/solicitante.php';
    }
}
