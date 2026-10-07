<?php

/**
 * MotivoModel.php
 * Modelo alineado exactamente con la tabla `motivos` de control_vehicular.
 */
class MotivoModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function obtenerTodos(): array
    {
        $sql = "SELECT id_motivo, descripcion, requiere_especificacion
                FROM motivos
                ORDER BY descripcion ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
}
