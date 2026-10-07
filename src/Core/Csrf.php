<?php

namespace Core;

/**
 * Csrf.php
 * Clase de protección contra falsificación de peticiones en sitios cruzados (Anti-CSRF).
 */
class Csrf
{
    public const TOKEN_KEY = 'csrf_token';

    /**
     * Genera una cadena aleatoria segura (64 caracteres hex) y la almacena en $_SESSION['csrf_token'] si no existe.
     */
    public static function generateToken(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (empty($_SESSION[self::TOKEN_KEY])) {
            $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::TOKEN_KEY];
    }

    /**
     * Compara el token recibido contra el de la sesión usando hash_equals()
     * para mitigar ataques de tiempo (timing attacks).
     */
    public static function validateToken(?string $token): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (empty($_SESSION[self::TOKEN_KEY]) || !is_string($token) || $token === '') {
            return false;
        }

        return hash_equals($_SESSION[self::TOKEN_KEY], $token);
    }

    /**
     * Retorna el campo oculto HTML listo para inyectar en formularios.
     */
    public static function renderField(): string
    {
        $token = self::generateToken();
        return '<input type="hidden" name="' . self::TOKEN_KEY . '" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
}

// Alias para permitir invocación sin namespace: Csrf::generateToken(), Csrf::renderField()
if (!class_exists('Csrf', false)) {
    class_alias(\Core\Csrf::class, 'Csrf');
}
