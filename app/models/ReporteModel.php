<?php

require_once __DIR__ . '/DatabaseErrorHandler.php';

/**
 * ReporteModel.php
 * Modelo centralizado para la generación de reportes ejecutivos
 * de comisiones oficiales, itinerarios y bitácoras del sistema.
 */
class ReporteModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Obtiene el consolidado general de comisiones y solicitudes
     * con itinerario decodificable y métricas de kilometraje en caseta.
     */
    public function obtenerComisionesConItinerario(): array
    {
        $sql = "SELECT s.id_solicitud,
                       s.destino,
                       s.tipo_comision,
                       s.fecha_requerida,
                       s.hora_requerida,
                       s.fecha_retorno,
                       s.hora_retorno,
                       s.num_pasajeros,
                       s.especificacion_motivo,
                       s.itinerario_paradas,
                       s.estado_solicitud,
                       s.fecha_autorizacion,
                       s.comentarios_jefe,
                       u.nombre_completo        AS solicitante_nombre,
                       u.correo                 AS solicitante_correo,
                       a.nombre_area,
                       m.descripcion            AS motivo_descripcion,
                       v.placas                 AS vehiculo_placas,
                       v.numero_economico       AS vehiculo_economico,
                       v.marca                  AS vehiculo_marca,
                       v.modelo                 AS vehiculo_modelo,
                       j.nombre_completo        AS jefe_nombre,
                       mov.km_inicial,
                       mov.km_final,
                       mov.fecha_salida         AS fecha_hora_salida,
                       mov.fecha_retorno        AS fecha_hora_entrada,
                       CASE
                           WHEN mov.km_final IS NOT NULL AND mov.km_inicial IS NOT NULL AND mov.km_final >= mov.km_inicial
                           THEN (mov.km_final - mov.km_inicial)
                           ELSE NULL
                       END AS km_recorridos
                FROM solicitudes s
                INNER JOIN usuarios u ON s.id_usuario_solicitante = u.id_usuario
                LEFT JOIN areas a ON u.id_area = a.id_area
                LEFT JOIN motivos m ON s.id_motivo = m.id_motivo
                LEFT JOIN vehiculos v ON s.vehiculo_deseado = v.id_vehiculo
                LEFT JOIN usuarios j ON s.id_jefe_autoriza = j.id_usuario
                LEFT JOIN movimientos mov ON mov.id_solicitud = s.id_solicitud
                ORDER BY s.id_solicitud DESC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
