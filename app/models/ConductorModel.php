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
