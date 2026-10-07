<?php

/**
 * public/dashboard.php
 * Acceso directo al dashboard que delega en el DashboardController.
 */

require_once __DIR__ . '/../app/controllers/DashboardController.php';

$controlador = new DashboardController();
$controlador->index();
