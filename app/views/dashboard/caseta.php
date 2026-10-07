<?php
/**
 * app/views/dashboard/caseta.php
 * Panel Operativo de Caseta — Control de Salidas y Retornos Vehiculares.
 * Roles: ROL_ENCARGADO_VEHICULAR / ROL_ADMINISTRADOR.
 * Zero CSS/JS inline: estilos en dashboard.css, lógica en main.js.
 */

require_once __DIR__ . '/../../../includes/header.php';
require_once __DIR__ . '/../../../includes/sidebar.php';

// Conteos para KPIs
$totalAutorizadas  = count($solicitudesAutorizadas ?? []);
$totalEnRuta       = count($vehiculosEnRuta ?? []);
$totalMovimientos  = count($movimientos ?? []);
?>

<main class="app-main dashboard-main">
    <!-- ── Cabecera Institucional ──────────────────────────────────────── -->
    <header class="app-topbar dashboard-topbar">
        <div>
            <h1 class="dashboard-title">
                Control de Caseta — Despacho y Retornos
            </h1>
            <p class="dashboard-subtitle">
                SECOTED Durango — Panel Operativo de Movimientos Vehiculares
            </p>
        </div>
        <div class="topbar-right dashboard-topbar-right">
            <span class="topbar-date"><?php echo date('d/m/Y H:i:s'); ?></span>
        </div>
    </header>

    <div class="app-content dashboard-content">
        <?php require_once __DIR__ . '/../layouts/alertas.php'; ?>

        <!-- ── KPIs de Caseta ──────────────────────────────────────────── -->
        <div class="kpi-grid">
            <!-- KPI 1: Solicitudes Listas para Salida -->
            <div class="kpi-card">
                <div class="kpi-header">
                    <span class="kpi-title">Listas para Despacho</span>
                    <span class="kpi-icon-wrap kpi-icon-warning">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                    </span>
                </div>
                <div class="kpi-value font-monospace <?php echo $totalAutorizadas > 0 ? 'kpi-value--warning' : ''; ?>">
                    <?php echo $totalAutorizadas; ?>
                </div>
                <div class="kpi-footer">
                    <?php if ($totalAutorizadas > 0): ?>
                        <span class="kpi-chip kpi-chip--warning">Pendientes de salida</span>
                    <?php else: ?>
                        <span class="kpi-chip kpi-chip--neutral">Sin cola de espera</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- KPI 2: Vehículos en Ruta -->
            <div class="kpi-card">
                <div class="kpi-header">
                    <span class="kpi-title">Vehículos en Ruta</span>
                    <span class="kpi-icon-wrap kpi-icon-accent">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                    </span>
                </div>
                <div class="kpi-value font-monospace <?php echo $totalEnRuta > 0 ? 'kpi-value--warning' : ''; ?>">
                    <?php echo $totalEnRuta; ?>
                </div>
                <div class="kpi-footer">
                    <?php if ($totalEnRuta > 0): ?>
                        <span class="kpi-chip kpi-chip--info"><?php echo $totalEnRuta; ?> en tránsito</span>
                        <span class="kpi-footer-text">Retorno pendiente</span>
                    <?php else: ?>
                        <span class="kpi-chip kpi-chip--success">Caseta despejada</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- KPI 3: Movimientos Registrados (bitácora) -->
            <div class="kpi-card">
                <div class="kpi-header">
                    <span class="kpi-title">Movimientos Registrados</span>
                    <span class="kpi-icon-wrap kpi-icon-success">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    </span>
                </div>
                <div class="kpi-value font-monospace">
                    <?php echo $totalMovimientos; ?>
                </div>
                <div class="kpi-footer">
                    <span class="kpi-chip kpi-chip--neutral">Últimos 50 registros</span>
                </div>
            </div>

            <!-- KPI 4: Vehículos Disponibles -->
            <div class="kpi-card">
                <div class="kpi-header">
                    <span class="kpi-title">Unidades Disponibles</span>
                    <span class="kpi-icon-wrap kpi-icon-default">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </span>
                </div>
                <div class="kpi-value font-monospace">
                    <?php echo (int)($totalVehiculosDisp ?? 0); ?>
                </div>
                <div class="kpi-footer">
                    <span class="kpi-chip kpi-chip--success">En resguardo</span>
                    <span class="kpi-footer-text">Parque activo</span>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════════════ -->
        <!-- SECCIÓN A: Cola de Despacho — Solicitudes Listas para Salida     -->
        <!-- ══════════════════════════════════════════════════════════════════ -->
        <div class="content-card dashboard-table-card">
            <div class="card-header dashboard-table-header">
                <div>
                    <h3 class="dashboard-table-title">
                        Cola de Despacho — Solicitudes Autorizadas
                    </h3>
                    <span class="dashboard-table-desc">Comisiones aprobadas por Administración listas para despacho en caseta</span>
                </div>
            </div>
            <div class="card-body dashboard-table-body">
                <div class="table-responsive">
                    <table class="table table-hover modern-table dashboard-table">
                        <thead>
                            <tr class="dashboard-tr-head">
                                <th class="dashboard-th">Folio Sol.</th>
                                <th class="dashboard-th">Conductor / Área</th>
                                <th class="dashboard-th">Vehículo Asignado</th>
                                <th class="dashboard-th">Destino / Motivo</th>
                                <th class="dashboard-th">Fecha Requerida</th>
                                <th class="dashboard-th">Odómetro Actual</th>
                                <th class="dashboard-th dashboard-th-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($solicitudesAutorizadas)): ?>
                                <?php foreach ($solicitudesAutorizadas as $sol): ?>
                                    <tr>
                                        <!-- Folio -->
                                        <td class="dashboard-td font-monospace">
                                            <strong>#<?php echo (int)($sol['id_solicitud'] ?? 0); ?></strong>
                                        </td>

                                        <!-- Conductor -->
                                        <td class="dashboard-td">
                                            <div class="dashboard-cell-title">
                                                <?php echo htmlspecialchars($sol['solicitante_nombre'] ?? 'Sin asignar', ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                            <small class="dashboard-cell-muted">
                                                <?php echo htmlspecialchars($sol['nombre_area'] ?? '—', ENT_QUOTES, 'UTF-8'); ?>
                                            </small>
                                        </td>

                                        <!-- Vehículo asignado -->
                                        <td class="dashboard-td">
                                            <span class="dashboard-plate-badge font-monospace">
                                                <?php echo htmlspecialchars($sol['vehiculo_placas'] ?? 'S/N', ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                            <span class="dashboard-cell-secondary">
                                                <?php echo htmlspecialchars(
                                                    trim(($sol['vehiculo_marca'] ?? '') . ' ' . ($sol['vehiculo_modelo'] ?? '')),
                                                    ENT_QUOTES, 'UTF-8'
                                                ); ?>
                                                (Eco <?php echo htmlspecialchars($sol['vehiculo_economico'] ?? 'S/N', ENT_QUOTES, 'UTF-8'); ?>)
                                            </span>
                                        </td>

                                        <!-- Destino / Motivo -->
                                        <td class="dashboard-td">
                                            <div>
                                                <strong><?php echo htmlspecialchars($sol['destino'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></strong>
                                            </div>
                                            <small class="dashboard-cell-muted">
                                                <?php echo htmlspecialchars($sol['motivo_descripcion'] ?? 'Oficial', ENT_QUOTES, 'UTF-8'); ?>
                                            </small>
                                            <?php
                                            $itinerarioCasetaRaw = $sol['itinerario_paradas'] ?? '';
                                            $paradasCaseta = !empty($itinerarioCasetaRaw) ? json_decode($itinerarioCasetaRaw, true) : null;
                                            if (is_array($paradasCaseta) && !empty($paradasCaseta)):
                                            ?>
                                                <div class="itinerario-flow" title="Trayecto autorizado con paradas intermedias">
                                                    <span class="itinerario-flow-step">Origen</span>
                                                    <?php foreach ($paradasCaseta as $p): ?>
                                                        <span class="itinerario-flow-arrow">&rarr;</span>
                                                        <span class="itinerario-flow-step itinerario-flow-step--stop" title="Motivo: <?php echo htmlspecialchars($p['motivo'] ?? 'Escala', ENT_QUOTES, 'UTF-8'); ?>">
                                                            <?php echo htmlspecialchars($p['ubicacion'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                        </span>
                                                    <?php endforeach; ?>
                                                    <span class="itinerario-flow-arrow">&rarr;</span>
                                                    <span class="itinerario-flow-step itinerario-flow-step--final"><?php echo htmlspecialchars($sol['destino'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Fecha Requerida -->
                                        <td class="dashboard-td font-monospace">
                                            <?php
                                            $fechaReq = !empty($sol['fecha_requerida']) ? date('d/m/Y', strtotime($sol['fecha_requerida'])) : '—';
                                            $horaReq  = !empty($sol['hora_requerida'])  ? substr($sol['hora_requerida'], 0, 5)                : '';
                                            echo htmlspecialchars(trim("{$fechaReq} {$horaReq}"), ENT_QUOTES, 'UTF-8');
                                            ?>
                                        </td>

                                        <!-- Odómetro actual del vehículo -->
                                        <td class="dashboard-td font-monospace">
                                            <?php echo number_format((int)($sol['vehiculo_km_actual'] ?? 0)); ?> km
                                        </td>

                                        <!-- Botón Despachar -->
                                        <td class="dashboard-td-center">
                                            <button
                                                type="button"
                                                class="btn btn-dark btn-sm btn-trigger-salida"
                                                data-id-solicitud="<?php echo (int)($sol['id_solicitud'] ?? 0); ?>"
                                                data-id-vehiculo="<?php echo (int)($sol['id_vehiculo'] ?? 0); ?>"
                                                data-conductor="<?php echo htmlspecialchars($sol['solicitante_nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                data-vehiculo="<?php echo htmlspecialchars(
                                                    ($sol['vehiculo_marca'] ?? '') . ' ' . ($sol['vehiculo_modelo'] ?? '') . ' — ' . ($sol['vehiculo_placas'] ?? ''),
                                                    ENT_QUOTES, 'UTF-8'
                                                ); ?>"
                                                data-destino="<?php echo htmlspecialchars($sol['destino'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                data-km-actual="<?php echo (int)($sol['vehiculo_km_actual'] ?? 0); ?>"
                                                data-itinerario="<?php echo htmlspecialchars($itinerarioCasetaRaw, ENT_QUOTES, 'UTF-8'); ?>"
                                                title="Registrar despacho de salida para esta solicitud"
                                                aria-label="Despachar solicitud #<?php echo (int)($sol['id_solicitud'] ?? 0); ?>">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/></svg>
                                                Despachar Salida
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="dashboard-empty-state">
                                        <svg class="dashboard-empty-icon" xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#CBD5E1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                                        <div class="dashboard-empty-title">Sin solicitudes pendientes de despacho</div>
                                        <div class="dashboard-empty-desc">No hay comisiones autorizadas en espera de salida en este momento.</div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════════════ -->
        <!-- SECCIÓN B: Monitor de Vehículos en Ruta (Retorno Pendiente)      -->
        <!-- ══════════════════════════════════════════════════════════════════ -->
        <div class="content-card dashboard-table-card">
            <div class="card-header dashboard-table-header">
                <div>
                    <h3 class="dashboard-table-title">
                        Monitoreo en Tiempo Real — Unidades en Ruta
                    </h3>
                    <span class="dashboard-table-desc">Vehículos actualmente en tránsito con retorno pendiente de registro</span>
                </div>
            </div>
            <div class="card-body dashboard-table-body">
                <div class="table-responsive">
                    <table class="table table-hover modern-table dashboard-table">
                        <thead>
                            <tr class="dashboard-tr-head">
                                <th class="dashboard-th">Folio Mov.</th>
                                <th class="dashboard-th">Vehículo / Placas</th>
                                <th class="dashboard-th">Conductor</th>
                                <th class="dashboard-th">Destino / Motivo</th>
                                <th class="dashboard-th">Hora de Salida</th>
                                <th class="dashboard-th">Km Inicial</th>
                                <th class="dashboard-th dashboard-th-center">Registrar Retorno</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($vehiculosEnRuta)): ?>
                                <?php foreach ($vehiculosEnRuta as $mov): ?>
                                    <tr>
                                        <!-- Folio Movimiento -->
                                        <td class="dashboard-td font-monospace">
                                            <strong>#<?php echo (int)($mov['id_movimiento'] ?? 0); ?></strong>
                                            <?php if (!empty($mov['id_solicitud'])): ?>
                                                <small class="dashboard-cell-muted">Sol. #<?php echo (int)$mov['id_solicitud']; ?></small>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Vehículo / Placas -->
                                        <td class="dashboard-td">
                                            <div class="dashboard-cell-title">
                                                #<?php echo htmlspecialchars($mov['vehiculo_economico'] ?? 'S/N', ENT_QUOTES, 'UTF-8'); ?>
                                                — <?php echo htmlspecialchars(
                                                    trim(($mov['vehiculo_marca'] ?? '') . ' ' . ($mov['vehiculo_modelo'] ?? '')),
                                                    ENT_QUOTES, 'UTF-8'
                                                ); ?>
                                            </div>
                                            <span class="font-monospace dashboard-plate-badge">
                                                <?php echo htmlspecialchars($mov['vehiculo_placas'] ?? 'N/D', ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </td>

                                        <!-- Conductor -->
                                        <td class="dashboard-td">
                                            <span class="dashboard-cell-secondary">
                                                <?php echo htmlspecialchars($mov['conductor_nombre'] ?? 'Sin asignar', ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </td>

                                        <!-- Destino / Motivo -->
                                        <td class="dashboard-td">
                                            <div><?php echo htmlspecialchars($mov['destino'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></div>
                                            <small class="dashboard-cell-muted">
                                                <?php echo htmlspecialchars($mov['motivo_descripcion'] ?? 'Oficial', ENT_QUOTES, 'UTF-8'); ?>
                                            </small>
                                        </td>

                                        <!-- Hora de Salida -->
                                        <td class="dashboard-td">
                                            <span class="font-monospace dashboard-cell-mono">
                                                <?php echo htmlspecialchars(
                                                    !empty($mov['fecha_hora_salida']) ? date('H:i', strtotime($mov['fecha_hora_salida'])) : '—',
                                                    ENT_QUOTES, 'UTF-8'
                                                ); ?> hrs
                                            </span>
                                            <small class="dashboard-cell-sub">
                                                <?php echo htmlspecialchars(
                                                    !empty($mov['fecha_hora_salida']) ? date('d/m/Y', strtotime($mov['fecha_hora_salida'])) : '',
                                                    ENT_QUOTES, 'UTF-8'
                                                ); ?>
                                            </small>
                                        </td>

                                        <!-- Km Inicial -->
                                        <td class="dashboard-td font-monospace">
                                            <?php echo number_format((int)($mov['km_inicial'] ?? 0)); ?> km
                                        </td>

                                        <!-- Botón Registrar Retorno -->
                                        <td class="dashboard-td-center">
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-action--success btn-trigger-retorno"
                                                data-id-movimiento="<?php echo (int)($mov['id_movimiento'] ?? 0); ?>"
                                                data-vehiculo="<?php echo htmlspecialchars(
                                                    ($mov['vehiculo_marca'] ?? '') . ' ' . ($mov['vehiculo_modelo'] ?? '') . ' — ' . ($mov['vehiculo_placas'] ?? ''),
                                                    ENT_QUOTES, 'UTF-8'
                                                ); ?>"
                                                data-conductor="<?php echo htmlspecialchars($mov['conductor_nombre'] ?? 'Sin asignar', ENT_QUOTES, 'UTF-8'); ?>"
                                                data-km-inicial="<?php echo (int)($mov['km_inicial'] ?? 0); ?>"
                                                data-destino="<?php echo htmlspecialchars($mov['destino'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                title="Registrar retorno y entrada de la unidad a caseta"
                                                aria-label="Registrar retorno del movimiento #<?php echo (int)($mov['id_movimiento'] ?? 0); ?>">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                                                Registrar Retorno
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="dashboard-empty-state">
                                        <svg class="dashboard-empty-icon" xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#CBD5E1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                                        <div class="dashboard-empty-title">No hay unidades en ruta en este momento</div>
                                        <div class="dashboard-empty-desc">El parque vehicular se encuentra en resguardo. Todas las unidades han retornado.</div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════════════ -->
        <!-- SECCIÓN C: Bitácora de Movimientos Recientes                     -->
        <!-- ══════════════════════════════════════════════════════════════════ -->
        <div class="content-card dashboard-table-card">
            <div class="card-header dashboard-table-header">
                <div>
                    <h3 class="dashboard-table-title">Bitácora de Movimientos Recientes</h3>
                    <span class="dashboard-table-desc">Últimos 50 registros de salidas y retornos en caseta</span>
                </div>
                <div class="export-buttons-group">
                    <a href="index.php?action=movimientos_exportar_excel" class="btn-export-excel" title="Descargar bitácora en formato Excel">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="16" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                        Exportar Excel
                    </a>
                    <a href="index.php?action=movimientos_exportar_pdf" target="_blank" class="btn-export-pdf" title="Abrir reporte PDF en nueva pestaña">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                        Exportar PDF
                    </a>
                </div>
            </div>
            <div class="card-body dashboard-table-body">
                <div class="table-responsive">
                    <table class="table table-hover modern-table dashboard-table">
                        <thead>
                            <tr class="dashboard-tr-head">
                                <th class="dashboard-th">Folio</th>
                                <th class="dashboard-th">Vehículo</th>
                                <th class="dashboard-th">Conductor</th>
                                <th class="dashboard-th">Destino</th>
                                <th class="dashboard-th">Salida</th>
                                <th class="dashboard-th">Retorno</th>
                                <th class="dashboard-th dashboard-th-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($movimientos)): ?>
                                <?php foreach ($movimientos as $m): ?>
                                    <?php
                                    $estadoMov   = $m['estado_movimiento'] ?? 'Abierto';
                                    $chipClase   = $estadoMov === 'Abierto' ? 'kpi-chip--warning' : 'kpi-chip--success';
                                    $chipTexto   = $estadoMov === 'Abierto' ? 'En Ruta' : 'Concluido';
                                    ?>
                                    <tr>
                                        <!-- Folio -->
                                        <td class="dashboard-td font-monospace">
                                            <strong>#<?php echo (int)($m['id_movimiento'] ?? 0); ?></strong>
                                        </td>

                                        <!-- Vehículo -->
                                        <td class="dashboard-td">
                                            <span class="font-monospace dashboard-plate-badge">
                                                <?php echo htmlspecialchars($m['vehiculo_placas'] ?? 'N/D', ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                            <small class="dashboard-cell-muted">
                                                Eco <?php echo htmlspecialchars($m['vehiculo_economico'] ?? '—', ENT_QUOTES, 'UTF-8'); ?>
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
                                            <?php echo !empty($m['fecha_hora_salida'])
                                                ? htmlspecialchars(date('d/m/Y H:i', strtotime($m['fecha_hora_salida'])), ENT_QUOTES, 'UTF-8')
                                                : '—'; ?>
                                        </td>

                                        <!-- Fecha Retorno -->
                                        <td class="dashboard-td font-monospace">
                                            <?php echo !empty($m['fecha_hora_entrada'])
                                                ? htmlspecialchars(date('d/m/Y H:i', strtotime($m['fecha_hora_entrada'])), ENT_QUOTES, 'UTF-8')
                                                : '<span class="dashboard-cell-muted">Pendiente</span>'; ?>
                                        </td>

                                        <!-- Estado -->
                                        <td class="dashboard-td-center">
                                            <span class="kpi-chip <?php echo $chipClase; ?>">
                                                <?php if ($estadoMov === 'Abierto'): ?>
                                                    <span class="status-dot-amber"></span>
                                                <?php endif; ?>
                                                <?php echo htmlspecialchars($chipTexto, ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="dashboard-empty-state">
                                        <svg class="dashboard-empty-icon" xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#CBD5E1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                        <div class="dashboard-empty-title">La bitácora de movimientos está vacía</div>
                                        <div class="dashboard-empty-desc">No se han registrado movimientos vehiculares en el sistema.</div>
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

<!-- ══════════════════════════════════════════════════════════════════════ -->
<!-- MODAL A: Despacho de Salida                                          -->
<!-- ══════════════════════════════════════════════════════════════════════ -->
<div id="modalDespacheSalida" class="modal-overlay" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="modalSalidaTitle">
    <div class="modal-dialog">
        <div class="modal-header">
            <h4 class="modal-title" id="modalSalidaTitle">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/></svg>
                Registrar Despacho de Salida
            </h4>
            <button type="button" class="modal-close" data-close-modal title="Cerrar ventana">&times;</button>
        </div>
        <form action="index.php?action=movimiento_despachar" method="POST">
            <?php echo Csrf::renderField(); ?>
            <input type="hidden" name="id_solicitud" id="salida_id_solicitud" value="">
            <input type="hidden" name="id_vehiculo"  id="salida_id_vehiculo"  value="">
            <div class="modal-body">
                <!-- Resumen de la comisión (informativo) -->
                <div class="modal-info-box">
                    <div class="modal-desc-tight">
                        <strong>Comisión a Despachar:</strong>
                    </div>
                    <p class="modal-note" id="salida_resumen">
                        —
                    </p>
                </div>

                <!-- Desglose de Escalas Autorizadas -->
                <div id="salida_wrap_itinerario" class="itinerario-modal-box form-group-hidden mb-14">
                    <div class="itinerario-modal-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
                        Escalas Autorizadas en Ruta
                    </div>
                    <ul class="itinerario-modal-list" id="salida_lista_itinerario">
                    </ul>
                </div>

                <div class="form-grid-2">
                    <!-- Kilometraje Inicial -->
                    <div class="form-group-full">
                        <label for="salida_km_inicial" class="modal-input-label">
                            Kilometraje Inicial (Odómetro de Salida) <span>*</span>
                        </label>
                        <input
                            type="number"
                            name="km_inicial"
                            id="salida_km_inicial"
                            class="modal-input"
                            required
                            min="0"
                            placeholder="Ej. 15400"
                        >
                        <small class="dashboard-cell-muted" id="salida_km_actual_hint"></small>
                    </div>

                    <!-- Observaciones de Salida -->
                    <div class="form-group-full">
                        <label for="salida_observaciones" class="modal-input-label">Observaciones de Salida</label>
                        <textarea
                            name="observaciones"
                            id="salida_observaciones"
                            class="modal-input"
                            rows="2"
                            maxlength="500"
                            placeholder="Condición del vehículo, equipamiento especial, etc."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" data-close-modal>Cancelar</button>
                <button type="submit" class="btn-modal-submit" id="salida_btn_submit">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/></svg>
                    Confirmar Salida
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════════ -->
<!-- MODAL B: Registro de Retorno (Entrada)                               -->
<!-- ══════════════════════════════════════════════════════════════════════ -->
<div id="modalRegistroRetorno" class="modal-overlay" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="modalRetornoTitle">
    <div class="modal-dialog">
        <div class="modal-header">
            <h4 class="modal-title" id="modalRetornoTitle">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                Registrar Retorno de Unidad
            </h4>
            <button type="button" class="modal-close" data-close-modal title="Cerrar ventana">&times;</button>
        </div>
        <form action="index.php?action=movimiento_retorno" method="POST">
            <?php echo Csrf::renderField(); ?>
            <input type="hidden" name="id_movimiento" id="retorno_id_movimiento" value="">
            <div class="modal-body">
                <!-- Resumen del movimiento -->
                <div class="modal-info-box">
                    <div class="modal-desc-tight">
                        <strong>Unidad en Retorno:</strong>
                    </div>
                    <p class="modal-note" id="retorno_resumen">—</p>
                </div>

                <div class="form-grid-2">
                    <!-- Kilometraje Final -->
                    <div class="form-group-full">
                        <label for="retorno_km_final" class="modal-input-label">
                            Kilometraje Final (Odómetro de Entrada) <span>*</span>
                        </label>
                        <input
                            type="number"
                            name="km_final"
                            id="retorno_km_final"
                            class="modal-input"
                            required
                            min="0"
                            placeholder="Ej. 15650"
                        >
                        <small class="dashboard-cell-muted" id="retorno_km_inicial_hint"></small>
                    </div>

                    <!-- Observaciones de Retorno -->
                    <div class="form-group-full">
                        <label for="retorno_observaciones" class="modal-input-label">Observaciones de Retorno</label>
                        <textarea
                            name="observaciones"
                            id="retorno_observaciones"
                            class="modal-input"
                            rows="2"
                            maxlength="500"
                            placeholder="Condición de retorno, incidencias, daños, etc."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" data-close-modal>Cancelar</button>
                <button type="submit" class="btn-modal-submit" id="retorno_btn_submit">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                    Confirmar Retorno
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
