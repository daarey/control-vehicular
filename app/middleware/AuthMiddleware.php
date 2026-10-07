<?php

/**
 * AuthMiddleware.php
 * Verifica que exista una sesión de usuario activa antes de acceder a recursos protegidos.
 */
class AuthMiddleware
{
    /**
     * Inicia la sesión si no está iniciada.
     */
    public static function iniciarSesion(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_samesite', 'Lax');
            ini_set('session.use_only_cookies', 1);
            session_start();
        }
    }

    /**
     * Evita que el navegador guarde en caché páginas protegidas
     * (soluciona el problema del botón "atrás" tras cerrar sesión).
     */
    private static function evitarCacheNavegador(): void
    {
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");
    }

    /**
     * Verifica si el usuario ha iniciado sesión.
     * Si no, lo redirige al login.
     */
    public static function verificarSesion(string $redirigirA = 'index.php'): void
    {
        self::iniciarSesion();
        self::evitarCacheNavegador();

        if (!isset($_SESSION['id_usuario']) || empty($_SESSION['id_usuario'])) {
            header("Location: {$redirigirA}?error=sesion_requerida");
            exit();
        }
    }

    /**
     * Comprueba si el usuario está autenticado sin forzar redirección.
     */
    public static function estaAutenticado(): bool
    {
        self::iniciarSesion();
        return isset($_SESSION['id_usuario']) && !empty($_SESSION['id_usuario']);
    }

    /**
     * Retorna el ID del usuario en sesión o null.
     */
    public static function idUsuario(): ?int
    {
        self::iniciarSesion();
        return isset($_SESSION['id_usuario']) ? (int)$_SESSION['id_usuario'] : null;
    }

    /**
     * Retorna el rol del usuario en sesión o null.
     */
    public static function idRol(): ?int
    {
        self::iniciarSesion();
        return isset($_SESSION['id_rol']) ? (int)$_SESSION['id_rol'] : null;
    }

    /**
     * Retorna el área del usuario en sesión o null.
     */
    public static function idArea(): ?int
    {
        self::iniciarSesion();
        return isset($_SESSION['id_area']) ? (int)$_SESSION['id_area'] : null;
    }
}