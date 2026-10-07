<?php
require_once __DIR__ . '/../../../includes/header.php';
require_once __DIR__ . '/../../../includes/sidebar.php';
?>

<main class="app-main">
    <header class="app-topbar">
        <div class="topbar-title">Reportes y Bitácora de Auditoría</div>
        <div class="topbar-right">
            <span class="topbar-date"><?php echo date('d/m/Y H:i:s'); ?></span>
        </div>
    </header>

    <div class="app-content">
        <?php require_once __DIR__ . '/../layouts/alertas.php'; ?>
        <div class="content-card">
            <div class="card-header">
                <h3>Bitácora de Eventos y Auditoría del Sistema</h3>
                <div class="export-buttons-group">
                    <a href="index.php?action=reportes_exportar_excel" class="btn-export-excel" title="Descargar bitácora de auditoría en formato Excel">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="16" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                        Exportar Excel
                    </a>
                    <a href="index.php?action=reportes_exportar_pdf" target="_blank" class="btn-export-pdf" title="Abrir reporte PDF de auditoría en nueva pestaña">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                        Exportar PDF
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Fecha y Hora</th>
                                <th>Usuario</th>
                                <th>Tabla / Entidad</th>
                                <th>ID Registro</th>
                                <th>Acción</th>
                                <th>Detalle</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($bitacora)): ?>
                                <?php foreach ($bitacora as $b): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($b['fecha'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($b['usuario_nombre'] ?? 'Usuario', ENT_QUOTES, 'UTF-8'); ?></strong>
                                            <small class="text-muted-block"><?php echo htmlspecialchars($b['usuario_correo'] ?? '', ENT_QUOTES, 'UTF-8'); ?></small>
                                        </td>
                                        <td><span class="badge badge--info"><?php echo htmlspecialchars($b['tabla_afectada'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span></td>
                                        <td>#<?php echo (int)($b['id_registro'] ?? 0); ?></td>
                                        <td><strong><?php echo htmlspecialchars($b['accion'] ?? '', ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                        <td><?php echo htmlspecialchars($b['detalle'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="table-empty-state">
                                        No hay registros de auditoría registrados en la bitácora.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
