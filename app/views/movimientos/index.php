<?php
/**
 * app/views/movimientos/index.php
 * Vista de Bitácora Histórica de Movimientos Vehiculares.
 *
 * NOTA DE ARQUITECTURA (v3):
 * Esta vista es la pantalla de SOLO-LECTURA de la bitácora de movimientos.
 * Las operaciones de despacho (salida) y retorno (entrada) se realizan
 * exclusivamente en dashboard/caseta.php, invocada por MovimientoController::index()
 * y DashboardController::dashboardCaseta().
 *
 * Reglas de diseño:
 *   - Cero CSS inline (style="...").
 *   - Cero JavaScript inline (<script>...</script>).
 *   - Toda salida sanitizada con htmlspecialchars().
 *   - Clases de componentes del sistema de diseño (dashboard.css).
 */

require_once __DIR__ . '/../../../includes/header.php';
require_once __DIR__ . '/../../../includes/sidebar.php';
?>

<main class="app-main dashboard-main">
    <!-- ── Cabecera Institucional ──────────────────────────────────────── -->
    <header class="app-topbar dashboard-topbar">
        <div>
            <h1 class="dashboard-title">
                Bitácora de Movimientos Vehiculares
            </h1>
            <p class="dashboard-subtitle">
                SECOTED Durango — Registro histórico de salidas y retornos en caseta
            </p>
        </div>
        <div class="topbar-right dashboard-topbar-right">
            <span class="topbar-date"><?php echo date('d/m/Y H:i:s'); ?></span>
        </div>
    </header>

    <div class="app-content dashboard-content">
        <?php require_once __DIR__ . '/../layouts/alertas.php'; ?>

        <div class="content-card dashboard-table-card">
            <div class="card-header dashboard-table-header">
                <div>
                    <h3 class="dashboard-table-title">
                        Registro de Movimientos Vehiculares en Caseta
                    </h3>
                    <span class="dashboard-table-desc">
                        Bitácora histórica de salidas y retornos. Para despachar o registrar retorno, use el
                        <a href="index.php?action=caseta" class="link-inline">Panel Operativo de Caseta</a>.
                    </span>
                </div>
                <div class="card-header-actions">
                    <a href="index.php?action=movimientos_exportar_excel"
                       class="btn btn-dark btn-sm dashboard-btn-link"
                       title="Descargar bitácora de movimientos en formato Excel">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Exportar Excel
                    </a>
                    <a href="index.php?action=movimientos_exportar_pdf"
                       target="_blank"
                       class="btn btn-sm btn-action--neutral dashboard-btn-link"
                       title="Abrir formato oficial de impresión / PDF">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                        Imprimir / PDF
                    </a>
                </div>
            </div>

            <div class="card-body dashboard-table-body">
                <div class="table-responsive">
                    <table class="table table-hover modern-table dashboard-table">
                        <thead>
                            <tr class="dashboard-tr-head">
                                <th class="dashboard-th">Folio</th>
                                <th class="dashboard-th">Vehículo / Placas</th>
                                <th class="dashboard-th">Conductor</th>
                                <th class="dashboard-th">Destino</th>
                                <th class="dashboard-th">Salida</th>
                                <th class="dashboard-th">Km Inicial</th>
                                <th class="dashboard-th">Retorno</th>
                                <th class="dashboard-th">Km Final</th>
                                <th class="dashboard-th">Recorrido</th>
                                <th class="dashboard-th dashboard-th-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($movimientos)): ?>
                                <?php foreach ($movimientos as $m): ?>
                                    <?php
                                    $estMov     = $m['estado_movimiento'] ?? 'Abierto';
                                    $chipClase  = match($estMov) {
                                        'Cerrado' => 'kpi-chip--success',
                                        'Abierto' => 'kpi-chip--warning',
                                        default   => 'kpi-chip--neutral',
                                    };
                                    $chipTexto  = match($estMov) {
                                        'Cerrado' => 'Concluido',
                                        'Abierto' => 'En Ruta',
                                        default   => htmlspecialchars($estMov, ENT_QUOTES, 'UTF-8'),
                                    };
                                    ?>
                                    <tr>
                                        <!-- Folio -->
                                        <td class="dashboard-td font-monospace">
                                            <strong>#<?php echo (int)($m['id_movimiento'] ?? 0); ?></strong>
                                        </td>

                                        <!-- Vehículo / Placas -->
                                        <td class="dashboard-td">
                                            <span class="font-monospace dashboard-plate-badge">
                                                <?php echo htmlspecialchars($m['vehiculo_placas'] ?? 'S/P', ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                            <small class="dashboard-cell-muted">
                                                Eco #<?php echo htmlspecialchars($m['vehiculo_economico'] ?? '—', ENT_QUOTES, 'UTF-8'); ?>
                                            </small>
                                        </td>

                                        <!-- Conductor -->
                                        <td class="dashboard-td">
                                            <span class="dashboard-cell-secondary">
                                                <?php echo htmlspecialchars($m['conductor_nombre'] ?? 'Sin asignar', ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </td>

                                        <!-- Destino -->
                                        <td class="dashboard-td">
                                            <?php echo htmlspecialchars(mb_strimwidth($m['destino'] ?? '—', 0, 40, '…'), ENT_QUOTES, 'UTF-8'); ?>
                                        </td>

                                        <!-- Fecha Salida -->
                                        <td class="dashboard-td font-monospace">
                                            <?php
                                            if (!empty($m['fecha_hora_salida'])) {
                                                echo htmlspecialchars(date('d/m/Y H:i', strtotime($m['fecha_hora_salida'])), ENT_QUOTES, 'UTF-8');
                                            } else {
                                                echo '—';
                                            }
                                            ?>
                                        </td>

                                        <!-- Km Inicial -->
                                        <td class="dashboard-td font-monospace">
                                            <?php echo number_format((float)($m['km_inicial'] ?? 0)); ?> km
                                        </td>

                                        <!-- Fecha Retorno -->
                                        <td class="dashboard-td font-monospace">
                                            <?php
                                            if (!empty($m['fecha_hora_entrada'])) {
                                                echo htmlspecialchars(date('d/m/Y H:i', strtotime($m['fecha_hora_entrada'])), ENT_QUOTES, 'UTF-8');
                                            } else {
                                                echo '<span class="dashboard-cell-muted">En tránsito</span>';
                                            }
                                            ?>
                                        </td>

                                        <!-- Km Final -->
                                        <td class="dashboard-td font-monospace">
                                            <?php
                                            if ($m['km_final'] !== null) {
                                                echo number_format((float)$m['km_final']) . ' km';
                                            } else {
                                                echo '<span class="dashboard-cell-muted">—</span>';
                                            }
                                            ?>
                                        </td>

                                        <!-- Km Recorridos -->
                                        <td class="dashboard-td font-monospace">
                                            <?php
                                            if ($m['km_recorridos'] !== null) {
                                                echo number_format((float)$m['km_recorridos']) . ' km';
                                            } else {
                                                echo '<span class="dashboard-cell-muted">—</span>';
                                            }
                                            ?>
                                        </td>

                                        <!-- Estado -->
                                        <td class="dashboard-td-center">
                                            <span class="kpi-chip <?php echo $chipClase; ?>">
                                                <?php if ($estMov === 'Abierto'): ?>
                                                    <span class="status-dot-amber"></span>
                                                <?php endif; ?>
                                                <?php echo $chipTexto; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="dashboard-empty-state">
                                        <svg class="dashboard-empty-icon" xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#CBD5E1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                                        <div class="dashboard-empty-title">La bitácora de movimientos está vacía</div>
                                        <div class="dashboard-empty-desc">No hay registros de salidas o retornos en el sistema.</div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div><!-- /.app-content -->
</main>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
