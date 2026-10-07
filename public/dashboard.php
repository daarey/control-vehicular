<?php

/**
 * public/dashboard.php
 * Acceso directo al dashboard que delega en el DashboardController.
 * Resolución dinámica de rutas de assets según el directorio de invocación.
 */

// Resolución dinámica de la ruta de assets
$inPublicDir = (strpos($_SERVER['SCRIPT_NAME'], '/public/') !== false);
$pathToAssets = $inPublicDir ? 'assets/' : 'public/assets/';

require_once __DIR__ . '/../app/controllers/DashboardController.php';

$controlador = new DashboardController();
$controlador->index();
