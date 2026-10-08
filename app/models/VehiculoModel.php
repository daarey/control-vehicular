<?php

require_once __DIR__ . '/DatabaseErrorHandler.php';

/**
 * VehiculoModel.php
 * Modelo para la gestión y persistencia de unidades vehiculares.
 */
class VehiculoModel
{
    private $db;

    public function __construct($conexion)
    {
        $this->db = $conexion;
    }

    // Listar con ordenamiento y filtros
    public function obtenerTodos($orden = 'numero_economico', $dir = 'DESC', $estado = null)
    {
        $ordenesPermitidos = ['numero_economico', 'modelo_anio', 'km_actual', 'marca'];
        $orden = in_array($orden, $ordenesPermitidos) ? $orden : 'numero_economico';
        $dir = ($dir === 'ASC') ? 'ASC' : 'DESC';

        $sql = "SELECT v.*, u.nombre_completo AS resguardante_nombre 
                FROM vehiculos v 
                LEFT JOIN usuarios u ON v.id_resguardante = u.id_usuario
                WHERE v.estatus = 1";
        
        $params = [];
        if (!empty($estado)) {
            $sql .= " AND v.estado_operativo = :estado";
            $params[':estado'] = $estado;
        }

        $sql .= " ORDER BY v.$orden $dir";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cuenta el total de vehículos activos aplicando filtros de búsqueda multi-campo y estado.
     */
    public function contarTotal(?string $busqueda = null, ?string $estado = null): int
    {
        $sql = "SELECT COUNT(*) as total
                FROM vehiculos v
                LEFT JOIN usuarios u ON v.id_resguardante = u.id_usuario
                WHERE v.estatus = 1";
        $params = [];

        if (!empty($estado)) {
            $sql .= " AND v.estado_operativo = :estado";
            $params[':estado'] = $estado;
        }

        if (!empty($busqueda)) {
            $sql .= " AND (
                v.placas LIKE :b1
                OR v.modelo LIKE :b2
                OR v.marca LIKE :b3
                OR v.numero_economico LIKE :b4
                OR v.numero_patrimonial LIKE :b5
                OR v.numero_serie LIKE :b6
                OR u.nombre_completo LIKE :b7
            )";
            $termino = '%' . trim($busqueda) . '%';
            for ($i = 1; $i <= 7; $i++) {
                $params[":b{$i}"] = $termino;
            }
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($res['total'] ?? 0);
    }

    /**
     * Obtiene registros paginados con ordenamiento y filtros de búsqueda multi-campo.
     */
    public function obtenerPaginados(int $limite, int $offset, ?string $busqueda = null, ?string $estado = null, string $orden = 'id_vehiculo', string $dir = 'DESC'): array
    {
        $ordenesPermitidos = ['id_vehiculo', 'numero_economico', 'modelo_anio', 'km_actual', 'marca', 'placas'];
        $orden = in_array($orden, $ordenesPermitidos, true) ? $orden : 'id_vehiculo';
        $dir = (strtoupper($dir) === 'ASC') ? 'ASC' : 'DESC';

        $sql = "SELECT v.*, u.nombre_completo AS resguardante_nombre
                FROM vehiculos v
                LEFT JOIN usuarios u ON v.id_resguardante = u.id_usuario
                WHERE v.estatus = 1";
        $params = [];

        if (!empty($estado)) {
            $sql .= " AND v.estado_operativo = :estado";
            $params[':estado'] = $estado;
        }

        if (!empty($busqueda)) {
            $sql .= " AND (
                v.placas LIKE :b1
                OR v.modelo LIKE :b2
                OR v.marca LIKE :b3
                OR v.numero_economico LIKE :b4
                OR v.numero_patrimonial LIKE :b5
                OR v.numero_serie LIKE :b6
                OR u.nombre_completo LIKE :b7
            )";
            $termino = '%' . trim($busqueda) . '%';
            for ($i = 1; $i <= 7; $i++) {
                $params[":b{$i}"] = $termino;
            }
        }

        $sql .= " ORDER BY v.$orden $dir LIMIT :limite OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $param => $val) {
            $stmt->bindValue($param, $val, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }



    // Insertar nuevo vehículo con captura de PDOException
    public function registrar($datos)
    {
        try {
            $sql = "INSERT INTO vehiculos 
                    (numero_economico, marca, modelo, modelo_anio, placas, numero_patrimonial, numero_serie, km_actual, id_resguardante) 
                    VALUES (:numero, :marca, :modelo, :anio, :placas, :patrimonial, :serie, :km_actual, :resguardante)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':numero' => $datos['numero_economico'],
                ':marca' => $datos['marca'],
                ':modelo' => $datos['modelo'],
                ':anio' => $datos['modelo_anio'],
                ':placas' => $datos['placas'],
                ':patrimonial' => $datos['numero_patrimonial'] ?? '',
                ':serie' => $datos['numero_serie'] ?? '',
                ':km_actual' => !empty($datos['km_actual']) ? (int)$datos['km_actual'] : 0,
                ':resguardante' => !empty($datos['id_resguardante']) ? (int)$datos['id_resguardante'] : null
            ]);
        } catch (PDOException $e) {
            DatabaseErrorHandler::handle($e, 'registrar el vehículo');
        }
    }

    public function obtenerPorId(int $id): array|false
    {
        $sql = "SELECT v.*, u.nombre_completo as resguardante_nombre
                FROM vehiculos v
                LEFT JOIN usuarios u ON v.id_resguardante = u.id_usuario
                WHERE v.id_vehiculo = :id
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerDisponibles(): array
    {
        $sql = "SELECT * FROM vehiculos 
                WHERE estado_operativo = 'Disponible' AND estatus = 1 
                ORDER BY marca ASC, modelo ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contarVehiculos(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM vehiculos WHERE estatus = 1");
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($res['total'] ?? 0);
    }

    public function editarVehiculo(int $id, array $datos): bool
    {
        try {
            $sql = "UPDATE vehiculos SET 
                        numero_economico   = :numero_economico,
                        placas             = :placas,
                        marca              = :marca,
                        modelo             = :modelo,
                        modelo_anio        = :modelo_anio,
                        numero_patrimonial = :numero_patrimonial,
                        numero_serie       = :numero_serie,
                        km_actual          = :km_actual,
                        estado_operativo   = :estado_operativo,
                        id_resguardante    = :id_resguardante
                    WHERE id_vehiculo = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id'                 => $id,
                ':numero_economico'   => $datos['numero_economico'],
                ':placas'             => $datos['placas'],
                ':marca'              => $datos['marca'],
                ':modelo'             => $datos['modelo'],
                ':modelo_anio'        => $datos['modelo_anio'],
                ':numero_patrimonial' => $datos['numero_patrimonial'] ?? '',
                ':numero_serie'       => $datos['numero_serie'] ?? '',
                ':km_actual'          => $datos['km_actual'] ?? 0,
                ':estado_operativo'   => $datos['estado_operativo'],
                ':id_resguardante'    => !empty($datos['id_resguardante']) ? (int)$datos['id_resguardante'] : null
            ]);
        } catch (PDOException $e) {
            DatabaseErrorHandler::handle($e, 'actualizar el vehículo');
        }
    }

    public function actualizar(int $id, array $datos): bool
    {
        return $this->editarVehiculo($id, $datos);
    }

    public function eliminarVehiculo(int $id): bool
    {
        try {
            $sql = "UPDATE vehiculos SET estatus = 0 WHERE id_vehiculo = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            DatabaseErrorHandler::handle($e, 'eliminar el vehículo');
        }
    }

    public function eliminar(int $id): bool
    {
        return $this->eliminarVehiculo($id);
    }
}
