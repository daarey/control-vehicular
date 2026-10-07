<?php

/**
 * BitacoraModel.php
 * Modelo alineado exactamente con la tabla `bitacora` de control_vehicular.
 */
class BitacoraModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function registrar(int $idUsuario, string $tablaAfectada, int $idRegistro, string $accion, ?string $detalle = null): bool
    {
        try {
            $sql = "INSERT INTO bitacora (id_usuario, tabla_afectada, id_registro, accion, detalle, fecha)
                    VALUES (:id_usuario, :tabla_afectada, :id_registro, :accion, :detalle, NOW())";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id_usuario'      => $idUsuario,
                ':tabla_afectada'  => $tablaAfectada,
                ':id_registro'     => $idRegistro,
                ':accion'          => $accion,
                ':detalle'         => $detalle,
            ]);
        } catch (Throwable $e) {
            error_log("Error escribiendo en BitacoraModel: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerRecientes(int $limite = 30): array
    {
        $sql = "SELECT b.*, u.nombre_completo as usuario_nombre, u.correo as usuario_correo
                FROM bitacora b
                LEFT JOIN usuarios u ON b.id_usuario = u.id_usuario
                ORDER BY b.id_bitacora DESC
                LIMIT :limite";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function obtenerTodosParaReporte(): array
    {
        $sql = "SELECT b.*, u.nombre_completo as usuario_nombre, u.correo as usuario_correo
                FROM bitacora b
                LEFT JOIN usuarios u ON b.id_usuario = u.id_usuario
                ORDER BY b.id_bitacora DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
}
