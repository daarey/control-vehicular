<?php

/**
 * DashboardModel.php
 * Modelo de datos ejecutivos y agregaciones métricas para el Panel de Control.
 */
class DashboardModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Retorna el conjunto consolidado de métricas para las tarjetas KPI.
     */
    public function obtenerKpisGlobales(): array
    {
        // 1. Métricas de Vehículos
        $stmtVeh = $this->db->query("
            SELECT 
                COUNT(*) AS total,
                SUM(CASE WHEN estado_operativo = 'Disponible' THEN 1 ELSE 0 END) AS disponibles,
                SUM(CASE WHEN estado_operativo = 'En ruta' THEN 1 ELSE 0 END) AS en_ruta,
                SUM(CASE WHEN estado_operativo = 'En mantenimiento' THEN 1 ELSE 0 END) AS mantenimiento
            FROM vehiculos 
            WHERE estatus = 1
        ");
        $vehData = $stmtVeh->fetch(PDO::FETCH_ASSOC) ?: [];

        // 2. Solicitudes pendientes
        $stmtSol = $this->db->query("SELECT COUNT(*) FROM solicitudes WHERE estado_solicitud = 'Pendiente'");
        $solPendientes = (int)$stmtSol->fetchColumn();

        // 3. Salidas en caseta hoy y retornos pendientes
        $stmtCaseta = $this->db->query("
            SELECT 
                COUNT(CASE WHEN DATE(fecha_hora_salida) = CURDATE() THEN 1 END) AS hoy,
                COUNT(CASE WHEN estado_movimiento = 'Abierto' THEN 1 END) AS abiertos
            FROM movimientos
        ");
        $casetaData = $stmtCaseta->fetch(PDO::FETCH_ASSOC) ?: [];

        // 4. Choferes activos (usuarios con licencia registrada)
        $stmtCond = $this->db->query("SELECT COUNT(*) FROM usuarios WHERE estatus = 1 AND numero_licencia IS NOT NULL AND TRIM(numero_licencia) != ''");
        $conductoresActivos = (int)$stmtCond->fetchColumn();

        return [
            'total_vehiculos'        => (int)($vehData['total'] ?? 0),
            'vehiculos_disponibles'  => (int)($vehData['disponibles'] ?? 0),
            'vehiculos_en_ruta'      => (int)($vehData['en_ruta'] ?? 0),
            'vehiculos_mantenimiento'=> (int)($vehData['mantenimiento'] ?? 0),
            'solicitudes_pendientes' => $solPendientes,
            'movimientos_hoy'        => (int)($casetaData['hoy'] ?? 0),
            'retornos_pendientes'    => (int)($casetaData['abiertos'] ?? 0),
            'total_conductores'      => $conductoresActivos,
        ];
    }

    /**
     * Proporción de vehículos agrupados por estado operativo para Donut Chart.
     * Retorna: ['Disponibles' => X, 'En ruta' => Y, 'En mantenimiento' => Z]
     */
    public function obtenerConteoPorEstadoOperativo(): array
    {
        $stmt = $this->db->query("
            SELECT estado_operativo, COUNT(*) AS conteo 
            FROM vehiculos 
            WHERE estatus = 1 
            GROUP BY estado_operativo
        ");
        $raw = $stmt->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];

        return [
            'Disponibles'      => (int)($raw['Disponible'] ?? 0),
            'En ruta'          => (int)($raw['En ruta'] ?? 0),
            'En mantenimiento' => (int)($raw['En mantenimiento'] ?? 0),
        ];
    }

    /**
     * Conteo de solicitudes de comisión por estatus para Bar Chart.
     * Retorna: ['Autorizadas' => A, 'Pendientes' => B, 'Rechazadas' => C]
     */
    public function obtenerConteoPorEstatusSolicitudes(): array
    {
        $stmt = $this->db->query("
            SELECT estado_solicitud, COUNT(*) AS conteo 
            FROM solicitudes 
            GROUP BY estado_solicitud
        ");
        $raw = $stmt->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];

        return [
            'Autorizadas' => (int)($raw['Autorizada'] ?? 0),
            'Pendientes'  => (int)($raw['Pendiente'] ?? 0),
            'Rechazadas'  => (int)($raw['Rechazada'] ?? 0),
        ];
    }

    /**
     * Obtiene las unidades en ruta activa o salidas registradas hoy para el Monitor de Caseta.
     */
    public function obtenerSalidasActivasCaseta(): array
    {
        $sql = "SELECT m.id_movimiento,
                       m.fecha_hora_salida,
                       m.destino,
                       m.estado_movimiento,
                       v.numero_economico,
                       v.placas,
                       v.marca,
                       v.modelo,
                       COALESCE(uc.nombre_completo, us.nombre_completo, 'Chofer no asignado') AS conductor_nombre,
                       mtv.descripcion AS motivo_descripcion,
                       m.especificacion_motivo
                FROM movimientos m
                INNER JOIN vehiculos   v  ON m.id_vehiculo            = v.id_vehiculo
                LEFT  JOIN solicitudes s  ON m.id_solicitud           = s.id_solicitud
                LEFT  JOIN usuarios    uc ON m.id_usuario_conductor   = uc.id_usuario
                LEFT  JOIN usuarios    us ON s.id_usuario_solicitante = us.id_usuario
                LEFT  JOIN motivos     mtv ON m.id_motivo             = mtv.id_motivo
                WHERE m.estado_movimiento = 'Abierto' OR DATE(m.fecha_hora_salida) = CURDATE()
                ORDER BY (m.estado_movimiento = 'Abierto') DESC, m.fecha_hora_salida DESC
                LIMIT 15";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
