<?php

require_once __DIR__ . '/AuthMiddleware.php';

/**
 * RoleMiddleware.php
 * Control centralizado de autorización basada en roles (RBAC)
 * alineado exactamente con la tabla `roles` de la base de datos:
 * 
 * 1 -> Administrador
 * 2 -> Encargado de control vehicular
 * 3 -> Solicitante
 * 4 -> Jefe de área
 */
class RoleMiddleware
{
    public const ROL_ADMINISTRADOR        = 1;
    public const ROL_ENCARGADO_VEHICULAR  = 2;
    public const ROL_CASETA               = 2;
    public const ROL_SOLICITANTE          = 3;
    public const ROL_JEFE_AREA            = 4;

    /**
     * Mapeo descriptivo de roles según la BD `roles`.
     */
    public const NOMBRES_ROLES = [
        self::ROL_ADMINISTRADOR       => 'Administrador',
        self::ROL_ENCARGADO_VEHICULAR => 'Encargado de Control Vehicular',
        self::ROL_SOLICITANTE         => 'Solicitante',
        self::ROL_JEFE_AREA           => 'Jefe de Área',
    ];

    /**
     * Comprueba si el usuario autenticado tiene alguno de los roles permitidos.
     *
     * @param int|array $rolesPermitidos Un solo ID de rol o arreglo de IDs permitidos.
     */
    public static function tieneRol(int|array $rolesPermitidos): bool
    {
        AuthMiddleware::iniciarSesion();

        $rolActual = AuthMiddleware::idRol();
        if ($rolActual === null) {
            return false;
        }

        if (is_int($rolesPermitidos)) {
            return $rolActual === $rolesPermitidos;
        }

        return in_array($rolActual, $rolesPermitidos, true);
    }

    /**
     * Exige que el usuario tenga un rol específico.
     * Si no cumple, detiene con 403 o redirige al dashboard.
     */
    public static function requerirRol(int|array $rolesPermitidos, string $redirigirA = 'dashboard.php'): void
    {
        AuthMiddleware::verificarSesion();

        if (!self::tieneRol($rolesPermitidos)) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                http_response_code(403);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['error' => 'No tiene permisos para realizar esta acción.']);
                exit();
            }

            header("Location: {$redirigirA}?error=acceso_denegado");
            exit();
        }
    }

    public static function esAdmin(): bool
    {
        return self::tieneRol(self::ROL_ADMINISTRADOR);
    }

    public static function esEncargadoVehicular(): bool
    {
        return self::tieneRol(self::ROL_ENCARGADO_VEHICULAR);
    }

    public static function esSolicitante(): bool
    {
        return self::tieneRol(self::ROL_SOLICITANTE);
    }

    public static function esJefeArea(): bool
    {
        return self::tieneRol(self::ROL_JEFE_AREA);
    }

    public static function getNombreRol(?int $idRol = null): string
    {
        $idRol ??= AuthMiddleware::idRol();
        return self::NOMBRES_ROLES[$idRol] ?? 'Usuario';
    }
}
