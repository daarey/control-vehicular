<?php

require_once __DIR__ . '/DatabaseErrorHandler.php';

/**
 * AreaModel.php
 * Modelo alineado exactamente con la tabla `areas` de control_vehicular.
 */
class AreaModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function obtenerTodas(): array
    {
        $sql = "SELECT id_area, nombre_area, estatus
                FROM areas
                ORDER BY id_area ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function contarAreas(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM areas WHERE estatus = 1");
        return (int)($stmt->fetch()['total'] ?? 0);
    }

    public function crearArea(string $nombreArea): bool
    {
        try {
            $sql = "INSERT INTO areas (nombre_area, estatus) VALUES (:nombre_area, 1)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':nombre_area' => trim($nombreArea)]);
        } catch (PDOException $e) {
            DatabaseErrorHandler::handle($e, 'registrar el área');
        }
    }

    public function registrar(array|string $datos): bool
    {
        $nombre = is_array($datos) ? ($datos['nombre_area'] ?? '') : $datos;
        return $this->crearArea($nombre);
    }

    public function editarArea(int $id, array $datos): bool
    {
        try {
            $sql = "UPDATE areas SET
                        nombre_area = :nombre_area,
                        estatus = :estatus
                        WHERE id_area = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id'          => $id,
                ':nombre_area' => trim($datos['nombre_area']),
                ':estatus'     => isset($datos['estatus']) ? (int)$datos['estatus'] : 1
            ]);
        } catch (PDOException $e) {
            DatabaseErrorHandler::handle($e, 'actualizar el área');
        }
    }

    public function actualizar(int $id, array $datos): bool
    {
        return $this->editarArea($id, $datos);
    }

    public function eliminarArea(int $id): bool
    {
        try {
            $sql = "DELETE FROM areas WHERE id_area = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            DatabaseErrorHandler::handle($e, 'eliminar el área');
        }
    }

    public function eliminar(int $id): bool
    {
        return $this->eliminarArea($id);
    }
}
