<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($tituloDocumento ?? 'Bitácora de Auditoría', ENT_QUOTES, 'UTF-8'); ?> — SECOTED</title>
    <link rel="stylesheet" href="assets/css/print.css">
</head>
<body>

    <!-- Barra de Acciones (Solo visible en pantalla, oculta al imprimir) -->
    <div class="print-toolbar no-print">
        <div class="print-toolbar-info">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            <span>Vista de Impresión Oficial — Bitácora de Auditoría y Trazabilidad</span>
        </div>
        <div class="print-toolbar-actions">
            <button type="button" class="btn-print-action btn-print-primary" onclick="window.print();">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Imprimir / Guardar como PDF
            </button>
            <button type="button" class="btn-print-action btn-print-secondary" onclick="window.close();">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Cerrar
            </button>
        </div>
    </div>

    <!-- Hoja de Reporte Formal -->
    <div class="print-sheet">

        <!-- ═══ Encabezado Institucional con Logotipos ═══ -->
        <header class="print-header-logos">
            <div class="print-logo-left">
                <img src="assets/img/Logo_D.webp" alt="Gobierno del Estado de Durango">
            </div>
            <div class="print-header-center">
                <div class="print-header-gov">Gobierno del Estado de Durango</div>
                <div class="print-header-secretaria">SECRETARÍA DE CONTRALORÍA — SECOTED</div>
                <div class="print-header-modulo">Unidad de Tecnologías y Seguridad de la Información — Reporte Ejecutivo de Auditoría</div>
            </div>
            <div class="print-logo-right">
                <img src="assets/img/SECOTED_Logo.webp" alt="SECOTED — Secretaría de Contraloría del Estado de Durango">
            </div>
        </header>

        <!-- ═══ Bloque de Metadatos ═══ -->
        <div class="print-metadata-block">
            <div class="print-metadata-item">
                <span class="print-metadata-label">Fecha y Hora de Generación</span>
                <strong><?php echo date('d/m/Y H:i:s'); ?></strong>
            </div>
            <div class="print-metadata-item">
                <span class="print-metadata-label">Auditor Emisor</span>
                <strong><?php echo htmlspecialchars($_SESSION['nombre_completo'] ?? 'Administrador', ENT_QUOTES, 'UTF-8'); ?></strong>
            </div>
            <div class="print-metadata-item">
                <span class="print-metadata-label">Tipo de Reporte</span>
                <strong>Bitácora de Auditoría y Trazabilidad del Sistema</strong>
            </div>
            <div class="print-metadata-item">
                <span class="print-metadata-label">Total de Eventos</span>
                <strong><?php echo number_format((int)($totalEventos ?? 0)); ?></strong>
            </div>
        </div>

        <!-- Título del Documento -->
        <section class="print-title-section">
            <h2 class="print-report-title">Historial y Trazabilidad de Operaciones del Sistema</h2>
            <p class="print-report-subtitle">Evidencia digital e inalterable de creación, modificación y bajas en la plataforma institucional — SECOTED Durango.</p>
        </section>

        <!-- Tarjetas de Resumen (KPIs) -->
        <section class="print-stats-grid">
            <div class="print-stat-card">
                <div class="print-stat-label">Total Eventos</div>
                <div class="print-stat-value"><?php echo number_format((float)($totalEventos ?? 0)); ?></div>
            </div>
            <div class="print-stat-card">
                <div class="print-stat-label">Nuevos Registros</div>
                <div class="print-stat-value"><?php echo number_format((float)($totalCrear ?? 0)); ?></div>
            </div>
            <div class="print-stat-card">
                <div class="print-stat-label">Modificaciones</div>
                <div class="print-stat-value"><?php echo number_format((float)($totalActualizar ?? 0)); ?></div>
            </div>
            <div class="print-stat-card">
                <div class="print-stat-label">Bajas y Eliminaciones</div>
                <div class="print-stat-value"><?php echo number_format((float)($totalEliminar ?? 0)); ?></div>
            </div>
        </section>

        <!-- Tabla de Auditoría -->
        <table class="print-table">
            <thead>
                <tr>
                    <th>Fecha y Hora</th>
                    <th>Usuario Autorizado</th>
                    <th>Correo Institucional</th>
                    <th>Módulo / Tabla</th>
                    <th>ID Reg.</th>
                    <th>Acción</th>
                    <th>Detalle de la Operación</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($bitacora)): ?>
                    <?php foreach ($bitacora as $b): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($b['fecha'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><strong><?php echo htmlspecialchars($b['usuario_nombre'] ?? 'Usuario', ENT_QUOTES, 'UTF-8'); ?></strong></td>
                            <td><code><?php echo htmlspecialchars($b['usuario_correo'] ?? 'Sin correo', ENT_QUOTES, 'UTF-8'); ?></code></td>
                            <td>
                                <span class="print-badge print-badge--info">
                                    <?php echo htmlspecialchars($b['tabla_afectada'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </td>
                            <td>#<?php echo (int)($b['id_registro'] ?? 0); ?></td>
                            <td>
                                <?php 
                                    $acc = strtoupper($b['accion'] ?? '');
                                    $badgeClass = match($acc) {
                                        'CREAR'      => 'print-badge--success',
                                        'ACTUALIZAR' => 'print-badge--info',
                                        'ELIMINAR', 'BAJA' => 'print-badge--danger',
                                        'SALIDA', 'ENTRADA' => 'print-badge--warning',
                                        default      => 'print-badge--info',
                                    };
                                ?>
                                <span class="print-badge <?php echo $badgeClass; ?>">
                                    <?php echo htmlspecialchars($acc, ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($b['detalle'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="print-table-empty">
                            No se encontraron eventos de auditoría registrados en la bitácora.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pie de Documento Oficial -->
        <footer class="print-footer">
            <div class="print-footer-left">
                <span class="print-footer-system">Sistema Integral de Control Vehicular — SECOTED Durango</span>
                <span class="print-footer-validation">Documento oficial de auditoría y trazabilidad. La autenticidad de este reporte se valida mediante el sistema institucional.</span>
            </div>
            <div class="print-footer-page">
                Documento Oficial de Auditoría
            </div>
        </footer>
    </div>

</body>
</html>
