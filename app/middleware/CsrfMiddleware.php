<?php

/**
 * CsrfMiddleware.php
 * Protección Anti-CSRF (Cross-Site Request Forgery) para el sistema.
 *
 * Genera y valida tokens únicos por sesión usando random_bytes() y hash_equals()
 * para prevenir ataques de tiempo (timing attacks).
 */
require_once __DIR__ . '/../../src/Core/Csrf.php';

use Core\Csrf;

class CsrfMiddleware
{
    public const TOKEN_KEY = Csrf::TOKEN_KEY;

    public static function generateToken(): string
    {
        return Csrf::generateToken();
    }

    public static function validateToken(?string $token): bool
    {
        return Csrf::validateToken($token);
    }

    public static function renderField(): string
    {
        return Csrf::renderField();
    }

    /**
     * Verifica obligatoriamente el token CSRF en peticiones POST.
     * Soporta token por $_POST, encabezado HTTP X-CSRF-Token o cuerpo JSON.
     * Si la validación falla, detiene la ejecución con HTTP 403.
     */
    public static function verificarPost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $token = $_POST[self::TOKEN_KEY] ?? null;

        // Soporte para encabezado X-CSRF-Token
        if (!$token && isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
        }

        // Soporte para payloads JSON
        if (!$token) {
            $rawInput = file_get_contents('php://input');
            if (!empty($rawInput)) {
                $decoded = json_decode($rawInput, true);
                if (is_array($decoded) && isset($decoded[self::TOKEN_KEY])) {
                    $token = $decoded[self::TOKEN_KEY];
                }
            }
        }

        if (!self::validateToken($token)) {
            $esAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
                || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

            if ($esAjax) {
                http_response_code(403);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => false,
                    'error' => 'Token CSRF inválido o expirado. Recargue la página e intente nuevamente.'
                ]);
            } else {
                http_response_code(403);
                if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['id_usuario'])) {
                    $_SESSION['alerta'] = [
                        'tipo' => 'danger',
                        'mensaje' => 'Token de seguridad inválido o expirado. Su solicitud fue rechazada por seguridad.'
                    ];
                    $referer = $_SERVER['HTTP_REFERER'] ?? 'index.php?action=dashboard';
                    header('Location: ' . $referer);
                } else {
                    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>403 - Acceso Denegado</title></head>';
                    echo '<body style="font-family:system-ui;text-align:center;padding:4rem;">';
                    echo '<h1 style="color:#dc3545;">403 — Solicitud Rechazada (CSRF Inválido)</h1>';
                    echo '<p>El token de seguridad es inválido o ha expirado. Por favor recargue la página e intente nuevamente.</p>';
                    echo '<a href="index.php" style="color:#0d6efd;">Volver al inicio</a>';
                    echo '</body></html>';
                }
            }
            exit();
        }
    }
}
