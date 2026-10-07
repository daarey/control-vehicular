<?php

require_once __DIR__ . '/DatabaseErrorHandler.php';

/**
 * UsuarioModel.php
 * Modelo para la gestión y persistencia de usuarios institucionales (SECOTED).
 * Implementa borrado lógico estricto (Soft Delete).
 * v2: Incluye soporte para número y vigencia de licencia de conducir.
 */
class UsuarioModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Obtiene la lista de todos los usuarios activos (estatus = 1).
     * Realiza LEFT JOIN con roles y áreas para incluir descripciones legibles.
     */
    public function obtenerTodos(): array
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
                ORDER BY u.id_usuario ASC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene el padrón de usuarios activos que cuentan con datos de licencia de conducir.
     */
    public function obtenerConductores(): array
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
                  AND TRIM(u.numero_licencia) != ''
                ORDER BY u.nombre_completo ASC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Obtiene los datos de un usuario por su ID (siempre que esté activo).
     */
    public function obtenerPorId(int $id): array|false
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
                       r.nombre_rol,
                       a.nombre_area
                FROM usuarios u
                LEFT JOIN roles r ON u.id_rol = r.id_rol
                LEFT JOIN areas a ON u.id_area = a.id_area
                WHERE u.id_usuario = :id AND u.estatus = 1
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Retorna el número y vigencia de la licencia de conducir de un usuario.
     */
    public function obtenerLicencia(int $id): array|false
    {
        $sql = "SELECT id_usuario, nombre_completo, numero_licencia, vigencia_licencia, foto_licencia
                FROM usuarios
                WHERE id_usuario = :id AND estatus = 1
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Actualiza el número, vigencia y fotografía de la licencia de conducir.
     */
    public function actualizarLicencia(int $id, ?string $numero, ?string $vigencia, ?string $foto = null): bool
    {
        try {
            if ($foto !== null) {
                $sql = "UPDATE usuarios
                        SET numero_licencia   = :numero_licencia,
                            vigencia_licencia = :vigencia_licencia,
                            foto_licencia     = :foto_licencia
                        WHERE id_usuario = :id AND estatus = 1";
                $params = [
                    ':id'                => $id,
                    ':numero_licencia'   => $numero ? trim($numero) : null,
                    ':vigencia_licencia' => $vigencia ?: null,
                    ':foto_licencia'     => $foto ? trim($foto) : null,
                ];
            } else {
                $sql = "UPDATE usuarios
                        SET numero_licencia   = :numero_licencia,
                            vigencia_licencia = :vigencia_licencia
                        WHERE id_usuario = :id AND estatus = 1";
                $params = [
                    ':id'                => $id,
                    ':numero_licencia'   => $numero ? trim($numero) : null,
                    ':vigencia_licencia' => $vigencia ?: null,
                ];
            }
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            DatabaseErrorHandler::handle($e, 'actualizar licencia del usuario');
        }
    }

    /**
     * Busca un usuario por correo electrónico (para autenticación y validación de duplicados).
     */
    public function buscarPorCorreo(string $correo): array|false
    {
        $sql = "SELECT id_usuario, nombre_completo, correo, password, id_rol, id_area, estatus
                FROM usuarios
                WHERE correo = :correo AND estatus = 1
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':correo' => trim($correo)]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Registra un nuevo usuario con estatus activo (estatus = 1).
     * Requiere: nombre_completo, correo, password (hash), id_rol, id_area.
     */
    public function registrar(array $datos): int|bool
    {
        try {
            $sql = "INSERT INTO usuarios (nombre_completo, correo, password, id_rol, id_area, estatus)
                    VALUES (:nombre_completo, :correo, :password, :id_rol, :id_area, 1)";

            $stmt = $this->db->prepare($sql);
            $exito = $stmt->execute([
                ':nombre_completo' => trim($datos['nombre_completo']),
                ':correo'          => trim($datos['correo']),
                ':password'        => $datos['password'],
                ':id_rol'          => (int)$datos['id_rol'],
                ':id_area'         => !empty($datos['id_area']) ? (int)$datos['id_area'] : null,
            ]);

            return $exito ? (int)$this->db->lastInsertId() : false;
        } catch (PDOException $e) {
            DatabaseErrorHandler::handle($e, 'registrar el usuario');
        }
    }

    /**
     * Modifica los datos de un usuario existente.
     * Regla: Si password viene vacío, se conserva la contraseña anterior.
     *        Si trae texto, se encripta con BCRYPT antes de persistir.
     */
    public function actualizar(int $id, array $datos): bool
    {
        try {
            if (!empty($datos['password'])) {
                $passwordHash = password_hash($datos['password'], PASSWORD_BCRYPT);
                $sql = "UPDATE usuarios SET
                            nombre_completo = :nombre_completo,
                            correo          = :correo,
                            password        = :password,
                            id_rol          = :id_rol,
                            id_area         = :id_area
                        WHERE id_usuario = :id AND estatus = 1";
                $params = [
                    ':id'              => $id,
                    ':nombre_completo' => trim($datos['nombre_completo']),
                    ':correo'          => trim($datos['correo']),
                    ':password'        => $passwordHash,
                    ':id_rol'          => (int)$datos['id_rol'],
                    ':id_area'         => !empty($datos['id_area']) ? (int)$datos['id_area'] : null,
                ];
            } else {
                $sql = "UPDATE usuarios SET
                            nombre_completo = :nombre_completo,
                            correo          = :correo,
                            id_rol          = :id_rol,
                            id_area         = :id_area
                        WHERE id_usuario = :id AND estatus = 1";
                $params = [
                    ':id'              => $id,
                    ':nombre_completo' => trim($datos['nombre_completo']),
                    ':correo'          => trim($datos['correo']),
                    ':id_rol'          => (int)$datos['id_rol'],
                    ':id_area'         => !empty($datos['id_area']) ? (int)$datos['id_area'] : null,
                ];
            }

            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            DatabaseErrorHandler::handle($e, 'actualizar el usuario');
        }
    }

    /**
     * Aplica borrado lógico (Soft Delete).
     * Prohibido usar DELETE: se actualiza el estatus a 0.
     */
    public function eliminar(int $id): bool
    {
        try {
            $sql = "UPDATE usuarios SET estatus = 0 WHERE id_usuario = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            DatabaseErrorHandler::handle($e, 'eliminar el usuario');
        }
    }

    /**
     * Obtiene el catálogo completo de roles para formularios modales.
     */
    public function obtenerRoles(): array
    {
        $sql = "SELECT id_rol, nombre_rol FROM roles ORDER BY id_rol ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
