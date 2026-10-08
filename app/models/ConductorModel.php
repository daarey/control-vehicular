<?php

require_once __DIR__ . '/DatabaseErrorHandler.php';
require_once __DIR__ . '/UsuarioModel.php';

/**
 * ConductorModel.php
 * Wrapper de compatibilidad hacia UsuarioModel.
 * La tabla `conductores` fue eliminada y el padrón de conductores consolidado en `usuarios`.
 */
class ConductorModel
{
    private PDO $db;
    private UsuarioModel $usuarioModel;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->usuarioModel = new UsuarioModel($db);
    }

    public function obtenerTodos(): array
    {
        return $this->usuarioModel->obtenerConductores();
    }

    /**
     * Cuenta total de conductores registrados con filtros de búsqueda.
     */
    public function contarTotal(?string $busqueda = null): int
    {
        $sql = "SELECT COUNT(*) as total
                FROM usuarios u
                LEFT JOIN roles r ON u.id_rol = r.id_rol
                LEFT JOIN areas a ON u.id_area = a.id_area
                WHERE u.estatus = 1
                  AND u.numero_licencia IS NOT NULL
                  AND TRIM(u.numero_licencia) != ''";
        $params = [];

        if (!empty($busqueda)) {
            $sql .= " AND (
                u.nombre_completo LIKE :b1
                OR u.correo LIKE :b2
                OR u.numero_licencia LIKE :b3
                OR a.nombre_area LIKE :b4
                OR r.nombre_rol LIKE :b5
            )";
            $termino = '%' . trim($busqueda) . '%';
            for ($i = 1; $i <= 5; $i++) {
                $params[":b{$i}"] = $termino;
            }
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($res['total'] ?? 0);
    }

    /**
     * Obtiene conductores paginados con búsqueda multi-campo ordenada desc por identificador.
     */
    public function obtenerPaginados(int $limite, int $offset, ?string $busqueda = null): array
    {
        $sql = "SELECT u.id_usuario,
                       u.nombre_completo,
                       u.correo,
                       u.id_rol,
                       u.id_area,
                       u.estatus,
                       u.numero_licencia,
                       u.vigencia_licencia,
                       u.foto_licencia,
                       COALESCE(r.nombre_rol, 'Sin rol') AS nombre_rol,
                       COALESCE(a.nombre_area, 'Sin área asignada') AS nombre_area
                FROM usuarios u
                LEFT JOIN roles r ON u.id_rol = r.id_rol
                LEFT JOIN areas a ON u.id_area = a.id_area
                WHERE u.estatus = 1
                  AND u.numero_licencia IS NOT NULL
                  AND TRIM(u.numero_licencia) != ''";
        $params = [];

        if (!empty($busqueda)) {
            $sql .= " AND (
                u.nombre_completo LIKE :b1
                OR u.correo LIKE :b2
                OR u.numero_licencia LIKE :b3
                OR a.nombre_area LIKE :b4
                OR r.nombre_rol LIKE :b5
            )";
            $termino = '%' . trim($busqueda) . '%';
            for ($i = 1; $i <= 5; $i++) {
                $params[":b{$i}"] = $termino;
            }
        }

        $sql .= " ORDER BY u.id_usuario DESC LIMIT :limite OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $param => $val) {
            $stmt->bindValue($param, $val, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function contarActivos(): int
    {
        $sql = "SELECT COUNT(*) as total FROM usuarios WHERE estatus = 1 AND numero_licencia IS NOT NULL AND TRIM(numero_licencia) != ''";
        $stmt = $this->db->query($sql);
        return (int)($stmt->fetch()['total'] ?? 0);
    }


    public function registrar(array $datos): bool
    {
        $id = (int)($datos['id_usuario'] ?? 0);
        if ($id <= 0) {
            return false;
        }
        return $this->usuarioModel->actualizarLicencia(
            $id,
            $datos['numero_licencia'] ?? null,
            $datos['vigencia_licencia'] ?? null,
            $datos['foto_licencia'] ?? null
        );
    }

    public function actualizar(int $id, array $datos): bool
    {
        return $this->usuarioModel->actualizarLicencia(
            $id,
            $datos['numero_licencia'] ?? null,
            $datos['vigencia_licencia'] ?? null,
            $datos['foto_licencia'] ?? null
        );
    }

    public function eliminar(int $id): bool
    {
        return $this->usuarioModel->actualizarLicencia($id, null, null, null);
    }
}
