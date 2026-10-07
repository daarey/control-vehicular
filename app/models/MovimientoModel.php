<?php

require_once __DIR__ . '/DatabaseErrorHandler.php';

/**
 * MovimientoModel.php
 * Modelo para la bitácora de entradas y salidas en caseta.
 * v3: Consolidado con tabla usuarios (id_usuario_conductor y solicitudes.id_usuario_solicitante).
 */
class MovimientoModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function obtenerRecientes(int $limite = 25): array
    {
        $sql = "SELECT m.*,
                       v.placas             AS vehiculo_placas,
                       v.numero_economico   AS vehiculo_economico,
                       v.marca              AS vehiculo_marca,
                       v.modelo             AS vehiculo_modelo,
                       COALESCE(uc.nombre_completo, us.nombre_completo, 'Chofer no asignado') AS conductor_nombre,
                       COALESCE(uc.numero_licencia, us.numero_licencia) AS conductor_licencia,
                       mtv.descripcion      AS motivo_descripcion,
                       u.nombre_completo    AS caseta_usuario
                FROM movimientos m
                LEFT JOIN vehiculos   v   ON m.id_vehiculo            = v.id_vehiculo
                LEFT JOIN solicitudes s   ON m.id_solicitud           = s.id_solicitud
                LEFT JOIN usuarios    uc  ON m.id_usuario_conductor   = uc.id_usuario
                LEFT JOIN usuarios    us  ON s.id_usuario_solicitante = us.id_usuario
                LEFT JOIN motivos     mtv ON m.id_motivo              = mtv.id_motivo
                LEFT JOIN usuarios    u   ON m.id_usuario_caseta      = u.id_usuario
                ORDER BY m.id_movimiento DESC
                LIMIT :limite";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function obtenerTodosParaReporte(): array
    {
        $sql = "SELECT m.*,
                       v.placas             AS vehiculo_placas,
                       v.numero_economico   AS vehiculo_economico,
                       v.marca              AS vehiculo_marca,
                       v.modelo             AS vehiculo_modelo,
                       COALESCE(uc.nombre_completo, us.nombre_completo, 'Chofer no asignado') AS conductor_nombre,
                       COALESCE(uc.numero_licencia, us.numero_licencia) AS conductor_licencia,
                       mtv.descripcion      AS motivo_descripcion,
                       u.nombre_completo    AS caseta_usuario,
                       s.itinerario_paradas AS itinerario_paradas,
                       CASE
                           WHEN m.km_final IS NOT NULL AND m.km_final >= m.km_inicial
                           THEN (m.km_final - m.km_inicial)
                           ELSE NULL
                       END AS km_recorridos
                FROM movimientos m
                LEFT JOIN vehiculos   v   ON m.id_vehiculo            = v.id_vehiculo
                LEFT JOIN solicitudes s   ON m.id_solicitud           = s.id_solicitud
                LEFT JOIN usuarios    uc  ON m.id_usuario_conductor   = uc.id_usuario
                LEFT JOIN usuarios    us  ON s.id_usuario_solicitante = us.id_usuario
                LEFT JOIN motivos     mtv ON m.id_motivo              = mtv.id_motivo
                LEFT JOIN usuarios    u   ON m.id_usuario_caseta      = u.id_usuario
                ORDER BY m.id_movimiento DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function contarMovimientosHoy(): int
    {
        $sql = "SELECT COUNT(*) as total FROM movimientos WHERE DATE(fecha_hora_salida) = CURDATE()";
        $stmt = $this->db->query($sql);
        return (int)($stmt->fetch()['total'] ?? 0);
    }

    public function contarAbiertos(): int
    {
        $sql = "SELECT COUNT(*) as total FROM movimientos WHERE estado_movimiento = 'Abierto'";
        $stmt = $this->db->query($sql);
        return (int)($stmt->fetch()['total'] ?? 0);
    }

    /**
     * Registra de forma transaccional y atómica la salida de un vehículo en caseta.
     * 1. Valida coherencia de odómetro (km_inicial >= km_actual del vehículo).
     * 2. Inserta el registro en `movimientos` con fecha_hora_salida = NOW() y estado 'Abierto'.
     * 3. Actualiza el vehículo a estado_operativo = 'En ruta' y ajusta km_actual.
     * 4. Actualiza la solicitud asociada a estado_solicitud = 'En curso'.
     *
     * @throws Exception Si el vehículo no existe, el kilometraje es inválido o falla la transacción.
     */
    public function registrarSalidaTransaccional(array $datos): int
    {
        $idVehiculo  = (int)($datos['id_vehiculo'] ?? 0);
        $kmInicial   = (int)($datos['km_inicial'] ?? 0);
        $idSolicitud = !empty($datos['id_solicitud']) ? (int)$datos['id_solicitud'] : null;

        if ($idVehiculo <= 0) {
            throw new Exception('Identificador de vehículo inválido para registrar salida.');
        }

        $this->db->beginTransaction();

        try {
            // 1. Validar odómetro actual del vehículo con bloqueo pesimista
            $stmtVeh = $this->db->prepare("SELECT id_vehiculo, km_actual, estado_operativo, numero_economico, placas FROM vehiculos WHERE id_vehiculo = :id FOR UPDATE");
            $stmtVeh->execute([':id' => $idVehiculo]);
            $vehiculo = $stmtVeh->fetch();

            if (!$vehiculo) {
                throw new Exception('El vehículo especificado no existe en el sistema.');
            }

            $kmActual = (int)($vehiculo['km_actual'] ?? 0);
            if ($kmInicial < $kmActual) {
                throw new Exception("El kilometraje de salida ({$kmInicial} km) no puede ser menor al odómetro actual de la unidad ({$kmActual} km).");
            }

            // 2. Si proviene de solicitud, validar que esté 'Autorizada'
            if ($idSolicitud !== null && $idSolicitud > 0) {
                $stmtSol = $this->db->prepare("SELECT id_solicitud, estado_solicitud, vehiculo_deseado FROM solicitudes WHERE id_solicitud = :id FOR UPDATE");
                $stmtSol->execute([':id' => $idSolicitud]);
                $solicitud = $stmtSol->fetch();

                if (!$solicitud) {
                    throw new Exception("La solicitud #{$idSolicitud} no existe.");
                }
                if ($solicitud['estado_solicitud'] !== 'Autorizada') {
                    throw new Exception("La solicitud #{$idSolicitud} no está en estado 'Autorizada' (estado actual: {$solicitud['estado_solicitud']}).");
                }
            }

            // 3. Insertar movimiento
            $sqlMov = "INSERT INTO movimientos (
                            id_solicitud, id_vehiculo, id_usuario_conductor,
                            id_motivo, especificacion_motivo, destino, km_inicial,
                            observaciones, id_usuario_caseta, estado_movimiento, fecha_hora_salida
                        ) VALUES (
                            :id_solicitud, :id_vehiculo, :id_usuario_conductor,
                            :id_motivo, :especificacion_motivo, :destino, :km_inicial,
                            :observaciones, :id_usuario_caseta, 'Abierto', NOW()
                        )";

            $stmtMov = $this->db->prepare($sqlMov);
            $stmtMov->execute([
                ':id_solicitud'          => $idSolicitud,
                ':id_vehiculo'           => $idVehiculo,
                ':id_usuario_conductor'  => !empty($datos['id_usuario_conductor'])  ? (int)$datos['id_usuario_conductor']  : null,
                ':id_motivo'             => (int)$datos['id_motivo'],
                ':especificacion_motivo' => $datos['especificacion_motivo'] ?? null,
                ':destino'               => $datos['destino']               ?? '',
                ':km_inicial'            => $kmInicial,
                ':observaciones'         => $datos['observaciones']         ?? null,
                ':id_usuario_caseta'     => (int)$datos['id_usuario_caseta'],
            ]);

            $idMovimiento = (int)$this->db->lastInsertId();

            // 4. Actualizar estado operativo del vehículo a 'En ruta' y actualizar km_actual
            $stmtUpdVeh = $this->db->prepare("UPDATE vehiculos SET estado_operativo = 'En ruta', km_actual = :km WHERE id_vehiculo = :id");
            $stmtUpdVeh->execute([
                ':km' => $kmInicial,
                ':id' => $idVehiculo,
            ]);

            // 5. Actualizar estado de la solicitud a 'En curso'
            if ($idSolicitud !== null && $idSolicitud > 0) {
                $stmtUpdSol = $this->db->prepare("UPDATE solicitudes SET estado_solicitud = 'En curso' WHERE id_solicitud = :id");
                $stmtUpdSol->execute([':id' => $idSolicitud]);
            }

            $this->db->commit();
            return $idMovimiento;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Registra de forma transaccional y atómica el retorno (entrada) de un vehículo a caseta.
     * 1. Valida que el movimiento esté 'Abierto' y que km_final >= km_inicial.
     * 2. Actualiza `movimientos`: fecha_hora_entrada = NOW(), km_final, estado_movimiento = 'Cerrado'.
     * 3. Actualiza el vehículo a estado_operativo = 'Disponible' y actualiza su odómetro (km_actual = km_final).
     * 4. Actualiza la solicitud a estado_solicitud = 'Concluida'.
     *
     * @throws Exception Si el movimiento no existe, el kilometraje es inválido o falla la transacción.
     */
    public function registrarEntradaTransaccional(int $idMovimiento, int $kmFinal, ?string $observaciones = null): bool
    {
        if ($idMovimiento <= 0) {
            throw new Exception('Identificador de movimiento inválido para registrar retorno.');
        }

        $this->db->beginTransaction();

        try {
            // 1. Bloqueo y lectura del movimiento
            $stmtMov = $this->db->prepare("SELECT * FROM movimientos WHERE id_movimiento = :id FOR UPDATE");
            $stmtMov->execute([':id' => $idMovimiento]);
            $mov = $stmtMov->fetch();

            if (!$mov) {
                throw new Exception("El movimiento #{$idMovimiento} no fue encontrado.");
            }
            if (($mov['estado_movimiento'] ?? '') !== 'Abierto') {
                throw new Exception("El movimiento #{$idMovimiento} ya se encuentra cerrado.");
            }

            $kmInicial = (int)($mov['km_inicial'] ?? 0);
            if ($kmFinal < $kmInicial) {
                throw new Exception("El kilometraje final ({$kmFinal} km) no puede ser inferior al kilometraje inicial registrado ({$kmInicial} km).");
            }

            // 2. Actualizar movimiento a 'Cerrado' con su odómetro de retorno
            $stmtUpdMov = $this->db->prepare("UPDATE movimientos 
                                              SET fecha_hora_entrada = NOW(),
                                                  km_final           = :km_final,
                                                  observaciones      = COALESCE(:observaciones, observaciones),
                                                  estado_movimiento  = 'Cerrado'
                                              WHERE id_movimiento = :id");
            $stmtUpdMov->execute([
                ':km_final'      => $kmFinal,
                ':observaciones' => $observaciones ?: null,
                ':id'            => $idMovimiento,
            ]);

            // 3. Actualizar vehículo a 'Disponible' con su odómetro final
            $idVehiculo = (int)$mov['id_vehiculo'];
            $stmtUpdVeh = $this->db->prepare("UPDATE vehiculos 
                                              SET estado_operativo = 'Disponible', 
                                                  km_actual        = :km 
                                              WHERE id_vehiculo = :id");
            $stmtUpdVeh->execute([
                ':km' => $kmFinal,
                ':id' => $idVehiculo,
            ]);

            // 4. Actualizar solicitud a 'Concluida' si proviene de comisión oficial
            $idSolicitud = !empty($mov['id_solicitud']) ? (int)$mov['id_solicitud'] : null;
            if ($idSolicitud !== null && $idSolicitud > 0) {
                $stmtUpdSol = $this->db->prepare("UPDATE solicitudes SET estado_solicitud = 'Concluida' WHERE id_solicitud = :id");
                $stmtUpdSol->execute([':id' => $idSolicitud]);
            }

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Alias retrocompatible de registrarSalidaTransaccional().
     */
    public function registrarSalida(array $datos): int
    {
        return $this->registrarSalidaTransaccional($datos);
    }

    /**
     * Alias retrocompatible de registrarEntradaTransaccional().
     */
    public function registrarEntrada(int $idMovimiento, int $kmFinal, ?string $observaciones = null): bool
    {
        return $this->registrarEntradaTransaccional($idMovimiento, $kmFinal, $observaciones);
    }

    /**
     * Obtiene las solicitudes en estado 'Autorizada' listas para despacho en caseta.
     */
    public function obtenerSolicitudesListasSalida(): array
    {
        $sql = "SELECT s.*,
                       u.nombre_completo        AS solicitante_nombre,
                       u.correo                 AS solicitante_correo,
                       u.numero_licencia        AS solicitante_licencia,
                       u.vigencia_licencia      AS solicitante_vigencia_licencia,
                       a.nombre_area,
                       m.descripcion            AS motivo_descripcion,
                       v.id_vehiculo,
                       v.numero_economico       AS vehiculo_economico,
                       v.placas                 AS vehiculo_placas,
                       v.marca                  AS vehiculo_marca,
                       v.modelo                 AS vehiculo_modelo,
                       v.km_actual              AS vehiculo_km_actual,
                       v.estado_operativo       AS vehiculo_estado_operativo
                FROM solicitudes s
                INNER JOIN usuarios  u ON s.id_usuario_solicitante = u.id_usuario
                LEFT  JOIN areas     a ON u.id_area               = a.id_area
                INNER JOIN motivos   m ON s.id_motivo             = m.id_motivo
                INNER JOIN vehiculos v ON s.vehiculo_deseado      = v.id_vehiculo
                WHERE s.estado_solicitud = 'Autorizada'
                ORDER BY s.fecha_requerida ASC, s.hora_requerida ASC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cuenta las solicitudes 'Autorizada' en espera de salida por caseta.
     */
    public function contarSolicitudesListasSalida(): int
    {
        $sql = "SELECT COUNT(*) as total
                FROM solicitudes s
                INNER JOIN usuarios  u ON s.id_usuario_solicitante = u.id_usuario
                INNER JOIN vehiculos v ON s.vehiculo_deseado      = v.id_vehiculo
                WHERE s.estado_solicitud = 'Autorizada'";

        $stmt = $this->db->query($sql);
        return (int)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
    }

    /**
     * Obtiene los vehículos que se encuentran actualmente en tránsito / en ruta.
     */
    public function obtenerVehiculosEnRuta(): array
    {
        $sql = "SELECT m.*,
                       v.placas             AS vehiculo_placas,
                       v.numero_economico   AS vehiculo_economico,
                       v.marca              AS vehiculo_marca,
                       v.modelo             AS vehiculo_modelo,
                       v.km_actual          AS vehiculo_km_actual,
                       COALESCE(uc.nombre_completo, us.nombre_completo, 'Chofer no asignado') AS conductor_nombre,
                       COALESCE(uc.numero_licencia, us.numero_licencia) AS conductor_licencia,
                       mtv.descripcion      AS motivo_descripcion,
                       u.nombre_completo    AS caseta_usuario
                FROM movimientos m
                INNER JOIN vehiculos   v   ON m.id_vehiculo            = v.id_vehiculo
                LEFT  JOIN solicitudes s   ON m.id_solicitud           = s.id_solicitud
                LEFT  JOIN usuarios    uc  ON m.id_usuario_conductor   = uc.id_usuario
                LEFT  JOIN usuarios    us  ON s.id_usuario_solicitante = us.id_usuario
                LEFT  JOIN motivos     mtv ON m.id_motivo              = mtv.id_motivo
                LEFT  JOIN usuarios    u   ON m.id_usuario_caseta      = u.id_usuario
                WHERE m.estado_movimiento = 'Abierto'
                ORDER BY m.fecha_hora_salida ASC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId(int $id): ?array
    {
        $sql = "SELECT m.*,
                       v.placas             AS vehiculo_placas,
                       v.numero_economico   AS vehiculo_economico,
                       v.marca              AS vehiculo_marca,
                       v.modelo             AS vehiculo_modelo,
                       v.km_actual          AS vehiculo_km_actual,
                       COALESCE(uc.nombre_completo, us.nombre_completo, 'Chofer no asignado') AS conductor_nombre,
                       COALESCE(uc.numero_licencia, us.numero_licencia)     AS conductor_licencia,
                       COALESCE(uc.vigencia_licencia, us.vigencia_licencia) AS conductor_vigencia_licencia,
                       s.estado_solicitud
                FROM movimientos m
                LEFT JOIN vehiculos   v  ON m.id_vehiculo            = v.id_vehiculo
                LEFT JOIN solicitudes s  ON m.id_solicitud           = s.id_solicitud
                LEFT JOIN usuarios    uc ON m.id_usuario_conductor   = uc.id_usuario
                LEFT JOIN usuarios    us ON s.id_usuario_solicitante = us.id_usuario
                WHERE m.id_movimiento = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Obtiene las unidades en ruta activa o salidas registradas hoy para el Monitor de Caseta en Tiempo Real.
     */
    public function obtenerSalidasActivasCaseta(int $limite = 15): array
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
                LIMIT :limite";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
