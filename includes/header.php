<?php
$titulo = $tituloPagina ?? 'Panel de Control — Control Vehicular';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema Institucional de Control Vehicular — SECOTED Durango">
    <title><?php echo htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8'); ?> | SECOTED</title>
    <meta name="csrf-token" content="<?php echo class_exists('CsrfMiddleware') ? CsrfMiddleware::generateToken() : (class_exists('Core\Csrf') ? \Core\Csrf::generateToken() : ''); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css?v=<?php echo file_exists(__DIR__ . '/../public/assets/css/styles.css') ? filemtime(__DIR__ . '/../public/assets/css/styles.css') : time(); ?>">
    <link rel="stylesheet" href="assets/css/dashboard.css?v=<?php echo file_exists(__DIR__ . '/../public/assets/css/dashboard.css') ? filemtime(__DIR__ . '/../public/assets/css/dashboard.css') : time(); ?>">
</head>
<body>
<div class="app-layout">
