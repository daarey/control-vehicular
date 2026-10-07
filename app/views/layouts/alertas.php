<?php
/**
 * alertas.php
 * Componente parcial de renderizado de alertas del sistema.
 * Consume $_SESSION['alerta'] con auto-limpieza al mostrarse.
 * Tono institucional y sobrio sin emojis.
 */
if (!isset($_SESSION['alerta'])) {
    if (!empty($_SESSION['flash_exito'])) {
        $_SESSION['alerta'] = ['tipo' => 'success', 'mensaje' => $_SESSION['flash_exito']];
        unset($_SESSION['flash_exito']);
    } elseif (!empty($_SESSION['flash_error'])) {
        $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => $_SESSION['flash_error']];
        unset($_SESSION['flash_error']);
    }
}
?>
<?php if (isset($_SESSION['alerta'])): 
    $tipoAlerta = htmlspecialchars($_SESSION['alerta']['tipo'] ?? 'info', ENT_QUOTES, 'UTF-8');
    $etiquetaAlerta = match($_SESSION['alerta']['tipo'] ?? 'info') {
        'danger'  => 'Atención:',
        'success' => 'Éxito:',
        'warning' => 'Aviso:',
        default   => 'Información:',
    };
?>
    <div class="alert alert-<?= $tipoAlerta ?> alert-dismissible fade show border-0 shadow-sm my-3" role="alert">
        <strong><?= $etiquetaAlerta ?></strong> 
        <?= htmlspecialchars($_SESSION['alerta']['mensaje'] ?? '', ENT_QUOTES, 'UTF-8') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
    <?php unset($_SESSION['alerta']); ?>
<?php endif; ?>
