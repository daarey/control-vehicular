<?php

require_once __DIR__ . '/DatabaseErrorHandler.php';

/**
 * SolicitudModel.php
 * Modelo alineado con la tabla `solicitudes` de control_vehicular.
 * v2: El vehículo es asignado por Administración (id_vehiculo inicia en NULL).
 *     Método asignarVehiculo() actualiza la unidad y cambia el estado a 'Autorizada'.
 */
class SolicitudModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function obtenerTodas(): array
    {
        $sql = "SELECT s.*, 
                       u.nombre_completo as solicitante_nombre, 
                       u.correo as solicitante_correo,
                       u.numero_licencia,
                       u.vigencia_licencia,
                       a.nombre_area,
                       m.descripcion as motivo_descripcion,
                       v.placas as vehiculo_placas,
                       v.marca as vehiculo_marca,
                       v.modelo as vehiculo_modelo,
                       v.numero_economico as vehiculo_eco,
                       j.nombre_completo as jefe_nombre
                FROM solicitudes s
                LEFT JOIN usuarios u ON s.id_usuario_solicitante = u.id_usuario
                LEFT JOIN areas a ON u.id_area = a.id_area
                LEFT JOIN motivos m ON s.id_motivo = m.id_motivo
                LEFT JOIN vehiculos v ON s.vehiculo_deseado = v.id_vehiculo
                LEFT JOIN usuarios j ON s.id_jefe_autoriza = j.id_usuario
                ORDER BY s.id_solicitud DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene solicitudes en estado 'Pendiente' para evaluación administrativa.
     */
    public function obtenerPendientes(?int $idArea = null): array
    {
        $sql = "SELECT s.*, 
                       u.nombre_completo as solicitante_nombre, 
                       u.correo as solicitante_correo,
                       u.numero_licencia,
                       u.vigencia_licencia,
                       a.nombre_area,
                       m.descripcion as motivo_descripcion,
                       v.placas as vehiculo_placas,
                       v.marca as vehiculo_marca,
                       v.modelo as vehiculo_modelo,
                       v.numero_economico as vehiculo_eco
                FROM solicitudes s
                INNER JOIN usuarios u ON s.id_usuario_solicitante = u.id_usuario
                LEFT JOIN areas a ON u.id_area = a.id_area
                LEFT JOIN motivos m ON s.id_motivo = m.id_motivo
                LEFT JOIN vehiculos v ON s.vehiculo_deseado = v.id_vehiculo
                WHERE s.estado_solicitud = 'Pendiente' ";

        if ($idArea !== null) {
            $sql .= " AND u.id_area = :id_area ";
        }

        $sql .= " ORDER BY s.fecha_requerida ASC, s.hora_requerida ASC";

        $stmt = $this->db->prepare($sql);
        if ($idArea !== null) {
            $stmt->bindValue(':id_area', $idArea, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cuenta solicitudes en estado 'Pendiente' para alertas en tiempo real.
     */
    public function contarPendientes(?int $idArea = null): int
    {
        $sql = "SELECT COUNT(*) as total
                FROM solicitudes s
                INNER JOIN usuarios u ON s.id_usuario_solicitante = u.id_usuario
                WHERE s.estado_solicitud = 'Pendiente'";

        if ($idArea !== null) {
            $sql .= " AND u.id_area = :id_area";
        }

        $stmt = $this->db->prepare($sql);
        if ($idArea !== null) {
            $stmt->bindValue(':id_area', $idArea, PDO::PARAM_INT);
        }
        $stmt->execute();
        return (int)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
    }

    /**
     * Obtiene solicitudes en estado 'Autorizada' o 'En curso' para seguimiento administrativo.
     */
    public function obtenerAutorizadasEnRuta(?int $idArea = null): array
    {
        $sql = "SELECT s.*, 
                       u.nombre_completo as solicitante_nombre, 
                       u.correo as solicitante_correo,
                       u.numero_licencia,
                       u.vigencia_licencia,
                       a.nombre_area,
                       m.descripcion as motivo_descripcion,
                       v.placas as vehiculo_placas,
                       v.marca as vehiculo_marca,
                       v.modelo as vehiculo_modelo,
                       v.numero_economico as vehiculo_eco,
                       j.nombre_completo as jefe_nombre
                FROM solicitudes s
                INNER JOIN usuarios u ON s.id_usuario_solicitante = u.id_usuario
                LEFT JOIN areas a ON u.id_area = a.id_area
                LEFT JOIN motivos m ON s.id_motivo = m.id_motivo
                LEFT JOIN vehiculos v ON s.vehiculo_deseado = v.id_vehiculo
                LEFT JOIN usuarios j ON s.id_jefe_autoriza = j.id_usuario
                WHERE s.estado_solicitud IN ('Autorizada', 'En curso') ";

        if ($idArea !== null) {
            $sql .= " AND u.id_area = :id_area ";
        }

        $sql .= " ORDER BY s.fecha_requerida DESC, s.hora_requerida DESC, s.id_solicitud DESC";

        $stmt = $this->db->prepare($sql);
        if ($idArea !== null) {
            $stmt->bindValue(':id_area', $idArea, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene comisiones concluidas, rechazadas o canceladas (Histórico).
     */
    public function obtenerHistorico(?int $idArea = null, int $limite = 200): array
    {
        $sql = "SELECT s.*, 
                       u.nombre_completo as solicitante_nombre, 
                       u.correo as solicitante_correo,
                       u.numero_licencia,
                       u.vigencia_licencia,
                       a.nombre_area,
                       m.descripcion as motivo_descripcion,
                       v.placas as vehiculo_placas,
                       v.marca as vehiculo_marca,
                       v.modelo as vehiculo_modelo,
                       v.numero_economico as vehiculo_eco,
                       j.nombre_completo as jefe_nombre,
                       mov.km_inicial,
                       mov.km_final,
                       CASE
                           WHEN mov.km_final IS NOT NULL AND mov.km_inicial IS NOT NULL AND mov.km_final >= mov.km_inicial
                           THEN (mov.km_final - mov.km_inicial)
                           ELSE NULL
                       END as km_recorridos
                FROM solicitudes s
                INNER JOIN usuarios u ON s.id_usuario_solicitante = u.id_usuario
                LEFT JOIN areas a ON u.id_area = a.id_area
                LEFT JOIN motivos m ON s.id_motivo = m.id_motivo
                LEFT JOIN vehiculos v ON s.vehiculo_deseado = v.id_vehiculo
                LEFT JOIN usuarios j ON s.id_jefe_autoriza = j.id_usuario
                LEFT JOIN movimientos mov ON mov.id_solicitud = s.id_solicitud
                WHERE s.estado_solicitud IN ('Concluida', 'Rechazada', 'Cancelada') ";

        if ($idArea !== null) {
            $sql .= " AND u.id_area = :id_area ";
        }

        $sql .= " ORDER BY s.id_solicitud DESC LIMIT :limite";

        $stmt = $this->db->prepare($sql);
        if ($idArea !== null) {
            $stmt->bindValue(':id_area', $idArea, PDO::PARAM_INT);
        }
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorUsuario(int $idUsuario): array
    {
        $sql = "SELECT s.*, 
                       m.descripcion as motivo_descripcion,
                       v.placas as vehiculo_placas,
                       v.marca as vehiculo_marca,
                       v.modelo as vehiculo_modelo,
                       v.numero_economico as vehiculo_eco
                FROM solicitudes s
                LEFT JOIN motivos m ON s.id_motivo = m.id_motivo
                LEFT JOIN vehiculos v ON s.vehiculo_deseado = v.id_vehiculo
                WHERE s.id_usuario_solicitante = :id_usuario
                ORDER BY s.id_solicitud DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_usuario' => $idUsuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorArea(int $idArea): array
    {
        $sql = "SELECT s.*, 
                       u.nombre_completo as solicitante_nombre,
                       u.correo as solicitante_correo,
                       u.numero_licencia,
                       u.vigencia_licencia,
                       m.descripcion as motivo_descripcion,
                       v.placas as vehiculo_placas,
                       v.marca as vehiculo_marca,
                       v.modelo as vehiculo_modelo,
                       v.numero_economico as vehiculo_eco
                FROM solicitudes s
                INNER JOIN usuarios u ON s.id_usuario_solicitante = u.id_usuario
                LEFT JOIN motivos m ON s.id_motivo = m.id_motivo
                LEFT JOIN vehiculos v ON s.vehiculo_deseado = v.id_vehiculo
                WHERE u.id_area = :id_area
                ORDER BY s.id_solicitud DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_area' => $idArea]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Autoriza una solicitud asignando la unidad disponible y registrando evaluador.
     */
    public function autorizar(int $idSolicitud, int $idJefe, ?string $comentarios = null): bool
    {
        try {
            $sql = "UPDATE solicitudes 
                    SET estado_solicitud   = 'Autorizada',
                        id_jefe_autoriza   = :id_jefe,
                        fecha_autorizacion = NOW(),
                        comentarios_jefe   = :comentarios
                    WHERE id_solicitud = :id_solicitud
                      AND estado_solicitud = 'Pendiente'";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id_solicitud' => $idSolicitud,
                ':id_jefe'      => $idJefe,
                ':comentarios'  => $comentarios,
            ]);
        } catch (PDOException $e) {
            DatabaseErrorHandler::handle($e, 'autorizar la solicitud');
        }
    }

    /**
     * Rechaza una solicitud en estado 'Pendiente' registrando la justificación.
     */
    public function rechazar(int $idSolicitud, ?int $idJefe = null, ?string $comentarios = null): bool
    {
        try {
            $sql = "UPDATE solicitudes 
                    SET estado_solicitud   = 'Rechazada',
                        id_jefe_autoriza   = :id_jefe,
                        fecha_autorizacion = NOW(),
                        comentarios_jefe   = :comentarios
                    WHERE id_solicitud = :id_solicitud
                      AND estado_solicitud = 'Pendiente'";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id_solicitud' => $idSolicitud,
                ':id_jefe'      => $idJefe,
                ':comentarios'  => $comentarios,
            ]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            DatabaseErrorHandler::handle($e, 'rechazar la solicitud');
        }
    }

    /**
     * Crea una nueva solicitud de comisión con datos logísticos completos.
     * El vehículo (vehiculo_deseado) se fuerza estrictamente a NULL; será asignado
     * posteriormente por Administración mediante asignarVehiculo().
     */
    public function crear(array $datos): int
    {
        try {
            $sql = "INSERT INTO solicitudes (
                        id_usuario_solicitante, id_motivo, especificacion_motivo,
                        itinerario_paradas, destino, tipo_comision, fecha_requerida,
                        hora_requerida, fecha_retorno, hora_retorno, num_pasajeros,
                        vehiculo_deseado, estado_solicitud
                    ) VALUES (
                        :id_usuario_solicitante, :id_motivo, :especificacion_motivo,
                        :itinerario_paradas, :destino, :tipo_comision, :fecha_requerida,
                        :hora_requerida, :fecha_retorno, :hora_retorno, :num_pasajeros,
                        NULL, 'Pendiente'
                    )";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id_usuario_solicitante' => (int)$datos['id_usuario_solicitante'],
                ':id_motivo'             => (int)$datos['id_motivo'],
                ':especificacion_motivo' => !empty($datos['especificacion_motivo']) ? trim($datos['especificacion_motivo']) : null,
                ':itinerario_paradas'    => !empty($datos['itinerario_paradas']) ? trim($datos['itinerario_paradas']) : null,
                ':destino'               => trim($datos['destino']),
                ':tipo_comision'         => in_array($datos['tipo_comision'] ?? '', ['Local', 'Foránea', 'Foranea'], true) ? $datos['tipo_comision'] : 'Local',
                ':fecha_requerida'       => $datos['fecha_requerida'],
                ':hora_requerida'        => $datos['hora_requerida'],
                ':fecha_retorno'         => !empty($datos['fecha_retorno']) ? $datos['fecha_retorno'] : null,
                ':hora_retorno'          => !empty($datos['hora_retorno']) ? $datos['hora_retorno'] : null,
                ':num_pasajeros'         => max(1, (int)($datos['num_pasajeros'] ?? 1)),
            ]);

            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            DatabaseErrorHandler::handle($e, 'registrar la solicitud');
        }
    }

    /**
     * Asigna un vehículo a una solicitud existente y la marca como 'Autorizada'.
     * Llamado por Administración cuando selecciona la unidad disponible.
     *
     * @param int         $idSolicitud  ID de la solicitud a actualizar.
     * @param int         $idVehiculo   ID del vehículo asignado (debe estar Disponible).
     * @param int|null    $idJefe       ID del evaluador/administrador que autoriza.
     * @param string|null $comentarios  Observaciones o justificación administrativa.
     */
    public function asignarVehiculo(int $idSolicitud, int $idVehiculo, ?int $idJefe = null, ?string $comentarios = null): bool
    {
        try {
            // Verificar disponibilidad real del vehículo seleccionado
            $check = $this->db->prepare("SELECT id_vehiculo FROM vehiculos WHERE id_vehiculo = :id_v AND estado_operativo = 'Disponible' AND estatus = 1 LIMIT 1");
            $check->execute([':id_v' => $idVehiculo]);
            if (!$check->fetch()) {
                throw new Exception('El vehículo seleccionado no se encuentra en estatus Disponible.');
            }

            $sql = "UPDATE solicitudes
                    SET vehiculo_deseado   = :id_vehiculo,
                        estado_solicitud   = 'Autorizada',
                        fecha_autorizacion = NOW(),
                        id_jefe_autoriza   = :id_jefe,
                        comentarios_jefe   = :comentarios
                    WHERE id_solicitud = :id_solicitud
                      AND estado_solicitud = 'Pendiente'";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id_solicitud' => $idSolicitud,
                ':id_vehiculo'  => $idVehiculo,
                ':id_jefe'      => $idJefe,
                ':comentarios'  => $comentarios,
            ]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            DatabaseErrorHandler::handle($e, 'asignar vehículo y autorizar la solicitud');
        }
    }

    public function registrar(array $datos): int
    {
        return $this->crear($datos);
    }

    /**
     * Obtiene una solicitud por su ID con datos completos de solicitante, licencia y unidad.
     */
    public function obtenerPorId(int $idSolicitud): ?array
    {
        $sql = "SELECT s.*, 
                       u.nombre_completo as solicitante_nombre, 
                       u.correo as solicitante_correo,
                       u.numero_licencia,
                       u.vigencia_licencia,
                       a.nombre_area,
                       m.descripcion as motivo_descripcion,
                       v.placas as vehiculo_placas,
                       v.marca as vehiculo_marca,
                       v.modelo as vehiculo_modelo,
                       v.numero_economico as vehiculo_eco
                FROM solicitudes s
                LEFT JOIN usuarios u ON s.id_usuario_solicitante = u.id_usuario
                LEFT JOIN areas a ON u.id_area = a.id_area
                LEFT JOIN motivos m ON s.id_motivo = m.id_motivo
                LEFT JOIN vehiculos v ON s.vehiculo_deseado = v.id_vehiculo
                WHERE s.id_solicitud = :id_solicitud
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_solicitud' => $idSolicitud]);
        $resultado = $stmt->fetch();
        return $resultado ?: null;
    }

    /**
     * Cancela una solicitud en estado 'Pendiente' perteneciente al solicitante.
     */
    public function cancelar(int $idSolicitud, int $idUsuario): bool
    {
        try {
            $sql = "UPDATE solicitudes 
                    SET estado_solicitud = 'Cancelada'
                    WHERE id_solicitud = :id_solicitud
                      AND id_usuario_solicitante = :id_usuario
                      AND estado_solicitud = 'Pendiente'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id_solicitud' => $idSolicitud,
                ':id_usuario'   => $idUsuario,
            ]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            DatabaseErrorHandler::handle($e, 'cancelar la solicitud');
        }
    }
}
