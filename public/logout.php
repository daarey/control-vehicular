<?php

/**
 * public/logout.php
 * Cierre de sesión seguro y destrucción de cookies de autenticación.
 */

require_once __DIR__ . '/../app/controllers/AuthController.php';

$controlador = new AuthController();
$controlador->cerrarSesion();
