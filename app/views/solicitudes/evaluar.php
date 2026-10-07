<?php
require_once __DIR__ . '/../../../includes/header.php';
require_once __DIR__ . '/../../../includes/sidebar.php';

$totalPendientes   = count($solicitudesPendientes ?? []);
$totalActivas      = count($solicitudesActivas ?? []);
$totalHistorico    = count($solicitudesHistorico ?? []);
$totalDisponibles  = count($vehiculosDisponibles ?? []);
?>

<main class="app-main dashboard-main">
    <!-- Cabecera Ejecutiva -->
    <header class="app-topbar dashboard-topbar">
        <div>
            <h1 class="dashboard-title">
                Evaluación y Gestión de Solicitudes
            </h1>
            <p class="dashboard-subtitle">
                Bandeja centralizada: evaluación de solicitudes pendientes, seguimiento de comisiones activas e historial
            </p>
        </div>
        <div class="topbar-right dashboard-topbar-right">
            <span class="topbar-date"><?php echo date('d/m/Y H:i:s'); ?></span>
        </div>
    </header>

    <div class="app-content dashboard-content">
        <?php require_once __DIR__ . '/../layouts/alertas.php'; ?>

        <!-- Métricas Rápidas de la Operación -->
        <div class="summary-kpi-bar">
            <div class="summary-kpi-pill summary-kpi-pill--warning">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                <div>
                    <div class="summary-kpi-pill-title">En Espera / Pendientes</div>
                    <div class="summary-kpi-pill-val font-monospace"><?php echo $totalPendientes; ?></div>
                </div>
            </div>

            <div class="summary-kpi-pill">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                <div>
                    <div class="summary-kpi-pill-title">Autorizadas / En Ruta</div>
                    <div class="summary-kpi-pill-val font-monospace"><?php echo $totalActivas; ?></div>
                </div>
            </div>

            <div class="summary-kpi-pill summary-kpi-pill--success">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                <div>
                    <div class="summary-kpi-pill-title">Unidades Disponibles</div>
                    <div class="summary-kpi-pill-val font-monospace"><?php echo $totalDisponibles; ?></div>
                </div>
            </div>

            <div class="summary-kpi-pill">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
                <div>
                    <div class="summary-kpi-pill-title">Histórico General</div>
                    <div class="summary-kpi-pill-val font-monospace"><?php echo $totalHistorico; ?></div>
                </div>
            </div>
        </div>

        <!-- Barra de Navegación por Pestañas -->
        <div class="eval-tabs-nav">
            <button type="button" class="eval-tab-btn active" data-tab-target="pane-pendientes">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
                <span>En Espera / Pendientes</span>
                <span class="eval-tab-badge eval-tab-badge--warning"><?php echo $totalPendientes; ?></span>
            </button>

            <button type="button" class="eval-tab-btn" data-tab-target="pane-activas">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                <span>Autorizadas / En Ruta</span>
                <span class="eval-tab-badge"><?php echo $totalActivas; ?></span>
            </button>

            <button type="button" class="eval-tab-btn" data-tab-target="pane-historico">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                <span>Histórico</span>
                <span class="eval-tab-badge"><?php echo $totalHistorico; ?></span>
            </button>
        </div>

        <!-- ══════════════════════════════════════════════════════════════════ -->
        <!-- PESTAÑA 1: En Espera / Pendientes (Vista Predeterminada)           -->
        <!-- ══════════════════════════════════════════════════════════════════ -->
        <div id="pane-pendientes" class="eval-tab-pane active">
            <div class="card dashboard-card">
                <div class="card-header dashboard-card-header">
                    <div>
                        <h3 class="dashboard-card-title">
                            Solicitudes en Espera de Evaluación
                        </h3>
                        <p class="dashboard-card-subtitle">
                            Inspeccione los requerimientos logísticos de la comisión y asigne obligatoriamente una unidad disponible
                        </p>
                    </div>
                </div>

                <div class="card-body dashboard-table-body">
                    <div class="table-responsive">
                        <table class="table table-hover modern-table">
                            <thead>
                                <tr class="dashboard-tr-head">
                                    <th class="dashboard-th">Folio / Solicitante</th>
                                    <th class="dashboard-th">Acreditación (Licencia)</th>
                                    <th class="dashboard-th">Destino y Tipo</th>
                                    <th class="dashboard-th">Horarios Programados</th>
                                    <th class="dashboard-th">Pasajeros y Motivo</th>
                                    <th class="dashboard-th dashboard-th-center">Acciones Administrativas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($solicitudesPendientes)): ?>
                                    <?php foreach ($solicitudesPendientes as $s): ?>
                                        <?php
                                            $idSol       = (int)$s['id_solicitud'];
                                            $solicitante = htmlspecialchars($s['solicitante_nombre'] ?? 'Sin nombre', ENT_QUOTES, 'UTF-8');
                                            $correo      = htmlspecialchars($s['solicitante_correo'] ?? '', ENT_QUOTES, 'UTF-8');
                                            $area        = htmlspecialchars($s['nombre_area'] ?? 'Área general', ENT_QUOTES, 'UTF-8');
                                            $destino     = htmlspecialchars($s['destino'] ?? 'No especificado', ENT_QUOTES, 'UTF-8');
                                            $tipoCom     = htmlspecialchars($s['tipo_comision'] ?? 'Local', ENT_QUOTES, 'UTF-8');
                                            $numLicencia = trim($s['numero_licencia'] ?? '');
                                            $vigLicencia = trim($s['vigencia_licencia'] ?? '');
                                            $pasajeros   = max(1, (int)($s['num_pasajeros'] ?? 1));
                                            $motivoDesc  = htmlspecialchars($s['motivo_descripcion'] ?? 'Oficial', ENT_QUOTES, 'UTF-8');
                                            $motivoEsp   = htmlspecialchars($s['especificacion_motivo'] ?? '', ENT_QUOTES, 'UTF-8');

                                            $salidaFecha  = !empty($s['fecha_requerida']) ? date('d/m/Y', strtotime($s['fecha_requerida'])) : 'N/D';
                                            $salidaHora   = !empty($s['hora_requerida']) ? substr($s['hora_requerida'], 0, 5) : '00:00';
                                            $retornoFecha = !empty($s['fecha_retorno']) ? date('d/m/Y', strtotime($s['fecha_retorno'])) : $salidaFecha;
                                            $retornoHora  = !empty($s['hora_retorno']) ? substr($s['hora_retorno'], 0, 5) : 'N/D';

                                            $licenciaValida = false;
                                            $licenciaBadgeClass = 'badge-license--missing';
                                            $licenciaTexto = 'Sin Licencia Registrada';

                                            if (!empty($numLicencia) && !empty($vigLicencia)) {
                                                if (strtotime($vigLicencia) >= strtotime('today')) {
                                                    $licenciaValida = true;
                                                    $licenciaBadgeClass = 'badge-license--valid';
                                                    $licenciaTexto = 'Vigente: ' . date('d/m/Y', strtotime($vigLicencia));
                                                } else {
                                                    $licenciaBadgeClass = 'badge-license--expired';
                                                    $licenciaTexto = 'Vencida: ' . date('d/m/Y', strtotime($vigLicencia));
                                                }
                                            }
                                        ?>
                                        <tr>
                                            <!-- Folio y Solicitante -->
                                            <td class="dashboard-td">
                                                <div class="dashboard-cell-title">
                                                    Folio #<?php echo $idSol; ?>
                                                </div>
                                                <div class="dashboard-cell-driver">
                                                    <?php echo $solicitante; ?>
                                                </div>
                                                <small class="dashboard-cell-muted">
                                                    <?php echo $area; ?>
                                                </small>
                                            </td>

                                            <!-- Licencia de Conducir -->
                                            <td class="dashboard-td">
                                                <div>
                                                    <span class="badge-license <?php echo $licenciaBadgeClass; ?>">
                                                        <?php echo $licenciaTexto; ?>
                                                    </span>
                                                </div>
                                                <?php if (!empty($numLicencia)): ?>
                                                    <small class="font-monospace dashboard-cell-sub">
                                                        No. <?php echo htmlspecialchars($numLicencia, ENT_QUOTES, 'UTF-8'); ?>
                                                    </small>
                                                <?php endif; ?>
                                            </td>

                                            <!-- Destino y Tipo -->
                                            <td class="dashboard-td">
                                                <div class="dashboard-cell-title">
                                                    <?php echo $destino; ?>
                                                </div>
                                                <div>
                                                    <?php if ($tipoCom === 'Foránea' || $tipoCom === 'Foranea'): ?>
                                                        <span class="badge badge--warning">Foránea</span>
                                                    <?php else: ?>
                                                        <span class="badge badge--info">Local</span>
                                                    <?php endif; ?>
                                                </div>
                                                <?php
                                                $itinerarioRaw = $s['itinerario_paradas'] ?? '';
                                                $paradasList = !empty($itinerarioRaw) ? json_decode($itinerarioRaw, true) : null;
                                                if (is_array($paradasList) && !empty($paradasList)):
                                                ?>
                                                    <div class="itinerario-flow" title="Trayecto oficial con paradas intermedias">
                                                        <span class="itinerario-flow-step">Origen</span>
                                                        <?php foreach ($paradasList as $p): ?>
                                                            <span class="itinerario-flow-arrow">&rarr;</span>
                                                            <span class="itinerario-flow-step itinerario-flow-step--stop" title="Motivo: <?php echo htmlspecialchars($p['motivo'] ?? 'Escala intermedia', ENT_QUOTES, 'UTF-8'); ?>">
                                                                <?php echo htmlspecialchars($p['ubicacion'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                            </span>
                                                        <?php endforeach; ?>
                                                        <span class="itinerario-flow-arrow">&rarr;</span>
                                                        <span class="itinerario-flow-step itinerario-flow-step--final"><?php echo $destino; ?></span>
                                                    </div>
                                                <?php endif; ?>
                                            </td>

                                            <!-- Horarios Programados -->
                                            <td class="dashboard-td">
                                                <div>
                                                    <span class="text-bold">Salida:</span>
                                                    <span class="font-monospace"><?php echo $salidaFecha . ' ' . $salidaHora; ?> hrs</span>
                                                </div>
                                                <small class="dashboard-cell-sub">
                                                    <span class="text-bold">Retorno:</span>
                                                    <span class="font-monospace"><?php echo $retornoFecha . ' ' . $retornoHora; ?> hrs</span>
                                                </small>
                                            </td>

                                            <!-- Pasajeros y Motivo -->
                                            <td class="dashboard-td">
                                                <div class="dashboard-cell-title">
                                                    <?php echo $motivoDesc; ?>
                                                </div>
                                                <?php if (!empty($motivoEsp)): ?>
                                                    <small class="dashboard-cell-muted">
                                                        <?php echo $motivoEsp; ?>
                                                    </small>
                                                <?php endif; ?>
                                                <div>
                                                    <small class="dashboard-cell-sub">
                                                        Pasajeros: <strong><?php echo $pasajeros; ?></strong>
                                                    </small>
                                                </div>
                                            </td>

                                            <!-- Acciones -->
                                            <td class="dashboard-td-center">
                                                <div class="table-actions">
                                                    <button type="button"
                                                            class="btn-action btn-action--edit btn-trigger-evaluar"
                                                            data-id="<?php echo $idSol; ?>"
                                                            data-folio="#<?php echo $idSol; ?>"
                                                            data-solicitante="<?php echo $solicitante; ?>"
                                                            data-area="<?php echo $area; ?>"
                                                            data-destino="<?php echo $destino; ?>"
                                                            data-tipo="<?php echo $tipoCom; ?>"
                                                            data-salida="<?php echo $salidaFecha . ' ' . $salidaHora . ' hrs'; ?>"
                                                            data-retorno="<?php echo $retornoFecha . ' ' . $retornoHora . ' hrs'; ?>"
                                                            data-pasajeros="<?php echo $pasajeros; ?>"
                                                            data-motivo="<?php echo $motivoDesc . (!empty($motivoEsp) ? ' (' . $motivoEsp . ')' : ''); ?>"
                                                            data-licencia-num="<?php echo htmlspecialchars($numLicencia ?: 'No registrada', ENT_QUOTES, 'UTF-8'); ?>"
                                                            data-licencia-texto="<?php echo $licenciaTexto; ?>"
                                                            data-licencia-valida="<?php echo $licenciaValida ? '1' : '0'; ?>"
                                                            data-itinerario="<?php echo htmlspecialchars($itinerarioRaw, ENT_QUOTES, 'UTF-8'); ?>">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                                        Evaluar / Asignar
                                                    </button>

                                                    <button type="button"
                                                            class="btn-action btn-action--delete btn-trigger-rechazar"
                                                            data-id="<?php echo $idSol; ?>"
                                                            data-folio="#<?php echo $idSol; ?>"
                                                            data-solicitante="<?php echo $solicitante; ?>"
                                                            data-destino="<?php echo $destino; ?>">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                                        Rechazar
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="dashboard-empty-state">
                                            <svg class="dashboard-empty-icon" xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#CBD5E1" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                                            <div class="dashboard-empty-title">No hay solicitudes pendientes de evaluación</div>
                                            <div class="dashboard-empty-desc">Todas las comisiones han sido procesadas o autorizadas.</div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════════════ -->
        <!-- PESTAÑA 2: Autorizadas / En Ruta                                   -->
        <!-- ══════════════════════════════════════════════════════════════════ -->
        <div id="pane-activas" class="eval-tab-pane">
            <div class="card dashboard-card">
                <div class="card-header dashboard-card-header">
                    <div>
                        <h3 class="dashboard-card-title">
                            Comisiones Autorizadas y en Ruta
                        </h3>
                        <p class="dashboard-card-subtitle">
                            Monitoreo de solicitudes con unidad oficial asignada listas para despacho en caseta o en trayecto operativo
                        </p>
                    </div>
                </div>

                <div class="card-body dashboard-table-body">
                    <div class="table-responsive">
                        <table class="table table-hover modern-table">
                            <thead>
                                <tr class="dashboard-tr-head">
                                    <th class="dashboard-th">Folio / Conductor</th>
                                    <th class="dashboard-th">Vehículo Asignado</th>
                                    <th class="dashboard-th">Destino y Tipo</th>
                                    <th class="dashboard-th">Horarios Programados</th>
                                    <th class="dashboard-th">Estatus Operativo</th>
                                    <th class="dashboard-th">Autorización</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($solicitudesActivas)): ?>
                                    <?php foreach ($solicitudesActivas as $act): ?>
                                        <?php
                                            $idAct       = (int)$act['id_solicitud'];
                                            $solicitante = htmlspecialchars($act['solicitante_nombre'] ?? 'Sin nombre', ENT_QUOTES, 'UTF-8');
                                            $area        = htmlspecialchars($act['nombre_area'] ?? 'Área general', ENT_QUOTES, 'UTF-8');
                                            $destino     = htmlspecialchars($act['destino'] ?? 'No especificado', ENT_QUOTES, 'UTF-8');
                                            $tipoCom     = htmlspecialchars($act['tipo_comision'] ?? 'Local', ENT_QUOTES, 'UTF-8');
                                            $estado      = $act['estado_solicitud'] ?? 'Autorizada';

                                            $vehPlacas   = htmlspecialchars($act['vehiculo_placas'] ?? 'N/D', ENT_QUOTES, 'UTF-8');
                                            $vehEco      = htmlspecialchars($act['vehiculo_eco'] ?? 'S/N', ENT_QUOTES, 'UTF-8');
                                            $vehModelo   = htmlspecialchars(($act['vehiculo_marca'] ?? '') . ' ' . ($act['vehiculo_modelo'] ?? ''), ENT_QUOTES, 'UTF-8');

                                            $salidaFecha  = !empty($act['fecha_requerida']) ? date('d/m/Y', strtotime($act['fecha_requerida'])) : 'N/D';
                                            $salidaHora   = !empty($act['hora_requerida']) ? substr($act['hora_requerida'], 0, 5) : '00:00';
                                            $retornoFecha = !empty($act['fecha_retorno']) ? date('d/m/Y', strtotime($act['fecha_retorno'])) : $salidaFecha;
                                            $retornoHora  = !empty($act['hora_retorno']) ? substr($act['hora_retorno'], 0, 5) : 'N/D';

                                            $jefeNombre   = htmlspecialchars($act['jefe_nombre'] ?? 'Administración', ENT_QUOTES, 'UTF-8');
                                            $fechaAut     = !empty($act['fecha_autorizacion']) ? date('d/m/Y H:i', strtotime($act['fecha_autorizacion'])) : '—';
                                        ?>
                                        <tr>
                                            <!-- Folio y Conductor -->
                                            <td class="dashboard-td">
                                                <div class="dashboard-cell-title">
                                                    Folio #<?php echo $idAct; ?>
                                                </div>
                                                <div class="dashboard-cell-driver">
                                                    <?php echo $solicitante; ?>
                                                </div>
                                                <small class="dashboard-cell-muted">
                                                    <?php echo $area; ?>
                                                </small>
                                            </td>

                                            <!-- Vehículo Asignado -->
                                            <td class="dashboard-td">
                                                <div>
                                                    <span class="dashboard-plate-badge font-monospace">
                                                        <?php echo $vehPlacas; ?>
                                                    </span>
                                                    <span class="kpi-chip kpi-chip--neutral">Eco #<?php echo $vehEco; ?></span>
                                                </div>
                                                <small class="dashboard-cell-sub">
                                                    <?php echo $vehModelo; ?>
                                                </small>
                                            </td>

                                            <!-- Destino y Tipo -->
                                            <td class="dashboard-td">
                                                <div class="dashboard-cell-title">
                                                    <?php echo $destino; ?>
                                                </div>
                                                <div>
                                                    <?php if ($tipoCom === 'Foránea' || $tipoCom === 'Foranea'): ?>
                                                        <span class="badge badge--warning">Foránea</span>
                                                    <?php else: ?>
                                                        <span class="badge badge--info">Local</span>
                                                    <?php endif; ?>
                                                </div>
                                                <?php
                                                $itinerarioActRaw = $act['itinerario_paradas'] ?? '';
                                                $paradasAct = !empty($itinerarioActRaw) ? json_decode($itinerarioActRaw, true) : null;
                                                if (is_array($paradasAct) && !empty($paradasAct)):
                                                ?>
                                                    <div class="itinerario-flow" title="Trayecto autorizado con paradas intermedias">
                                                        <span class="itinerario-flow-step">Origen</span>
                                                        <?php foreach ($paradasAct as $p): ?>
                                                            <span class="itinerario-flow-arrow">&rarr;</span>
                                                            <span class="itinerario-flow-step itinerario-flow-step--stop" title="Motivo: <?php echo htmlspecialchars($p['motivo'] ?? 'Escala', ENT_QUOTES, 'UTF-8'); ?>">
                                                                <?php echo htmlspecialchars($p['ubicacion'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                            </span>
                                                        <?php endforeach; ?>
                                                        <span class="itinerario-flow-arrow">&rarr;</span>
                                                        <span class="itinerario-flow-step itinerario-flow-step--final"><?php echo $destino; ?></span>
                                                    </div>
                                                <?php endif; ?>
                                            </td>

                                            <!-- Horarios -->
                                            <td class="dashboard-td">
                                                <div>
                                                    <span class="text-bold">Salida:</span>
                                                    <span class="font-monospace"><?php echo $salidaFecha . ' ' . $salidaHora; ?> hrs</span>
                                                </div>
                                                <small class="dashboard-cell-sub">
                                                    <span class="text-bold">Retorno:</span>
                                                    <span class="font-monospace"><?php echo $retornoFecha . ' ' . $retornoHora; ?> hrs</span>
                                                </small>
                                            </td>

                                            <!-- Estatus Operativo -->
                                            <td class="dashboard-td">
                                                <?php if ($estado === 'En curso'): ?>
                                                    <span class="table-badge-route">
                                                        <span class="pulse-dot-route" aria-hidden="true"></span>
                                                        En Ruta
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge--success">
                                                        Autorizada (En Caseta)
                                                    </span>
                                                <?php endif; ?>
                                            </td>

                                            <!-- Autorizado por -->
                                            <td class="dashboard-td">
                                                <div class="dashboard-cell-title">
                                                    <?php echo $jefeNombre; ?>
                                                </div>
                                                <small class="font-monospace dashboard-cell-sub">
                                                    <?php echo $fechaAut; ?>
                                                </small>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="dashboard-empty-state">
                                            <svg class="dashboard-empty-icon" xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#CBD5E1" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                                            <div class="dashboard-empty-title">No hay comisiones autorizadas o en ruta actualmente</div>
                                            <div class="dashboard-empty-desc">Las solicitudes aprobadas aparecerán aquí para control y despacho vehicular.</div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════════════ -->
        <!-- PESTAÑA 3: Histórico (Concluidas, Rechazadas, Canceladas)          -->
        <!-- ══════════════════════════════════════════════════════════════════ -->
        <div id="pane-historico" class="eval-tab-pane">
            <div class="card dashboard-card">
                <div class="card-header dashboard-card-header">
                    <div>
                        <h3 class="dashboard-card-title">
                            Histórico de Solicitudes y Comisiones Concluidas
                        </h3>
                        <p class="dashboard-card-subtitle">
                            Bitácora institucional de comisiones finalizadas, solicitudes no autorizadas y canceladas
                        </p>
                    </div>
                </div>

                <div class="card-body dashboard-table-body">
                    <div class="table-responsive">
                        <table class="table table-hover modern-table">
                            <thead>
                                <tr class="dashboard-tr-head">
                                    <th class="dashboard-th">Folio / Solicitante</th>
                                    <th class="dashboard-th">Destino y Motivo</th>
                                    <th class="dashboard-th">Fecha Requerida</th>
                                    <th class="dashboard-th">Unidad Asignada</th>
                                    <th class="dashboard-th">Estatus</th>
                                    <th class="dashboard-th">Dictamen / Observaciones</th>
                                    <th class="dashboard-th text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($solicitudesHistorico)): ?>
                                    <?php foreach ($solicitudesHistorico as $h): ?>
                                        <?php
                                            $idHist      = (int)$h['id_solicitud'];
                                            $solicitante = htmlspecialchars($h['solicitante_nombre'] ?? 'Sin nombre', ENT_QUOTES, 'UTF-8');
                                            $area        = htmlspecialchars($h['nombre_area'] ?? 'Área general', ENT_QUOTES, 'UTF-8');
                                            $destino     = htmlspecialchars($h['destino'] ?? 'No especificado', ENT_QUOTES, 'UTF-8');
                                            $motivo      = htmlspecialchars($h['motivo_descripcion'] ?? 'Oficial', ENT_QUOTES, 'UTF-8');
                                            $estado      = $h['estado_solicitud'] ?? 'Concluida';
                                            $comentarios = htmlspecialchars($h['comentarios_jefe'] ?? '', ENT_QUOTES, 'UTF-8');
                                            $jefe        = htmlspecialchars($h['jefe_nombre'] ?? '', ENT_QUOTES, 'UTF-8');

                                            $fechaReq = !empty($h['fecha_requerida']) ? date('d/m/Y', strtotime($h['fecha_requerida'])) : '—';
                                            $horaReq  = !empty($h['hora_requerida']) ? substr($h['hora_requerida'], 0, 5) : '';

                                            $vehPlacas = htmlspecialchars($h['vehiculo_placas'] ?? '', ENT_QUOTES, 'UTF-8');
                                            $vehEco    = htmlspecialchars($h['vehiculo_eco'] ?? '', ENT_QUOTES, 'UTF-8');

                                            $badgeEstadoClass = match ($estado) {
                                                'Concluida' => 'badge--success',
                                                'Rechazada' => 'badge--danger',
                                                'Cancelada' => 'badge--secondary',
                                                default     => 'badge--info'
                                            };
                                        ?>
                                        <tr>
                                            <!-- Folio y Solicitante -->
                                            <td class="dashboard-td">
                                                <div class="dashboard-cell-title">
                                                    Folio #<?php echo $idHist; ?>
                                                </div>
                                                <div class="dashboard-cell-driver">
                                                    <?php echo $solicitante; ?>
                                                </div>
                                                <small class="dashboard-cell-muted">
                                                    <?php echo $area; ?>
                                                </small>
                                            </td>

                                            <!-- Destino y Motivo -->
                                            <td class="dashboard-td">
                                                <div class="dashboard-cell-title">
                                                    <?php echo $destino; ?>
                                                </div>
                                                <small class="dashboard-cell-muted">
                                                    <?php echo $motivo; ?>
                                                </small>
                                                <?php
                                                $itinerarioHistRaw = $h['itinerario_paradas'] ?? '';
                                                $paradasHist = !empty($itinerarioHistRaw) ? json_decode($itinerarioHistRaw, true) : null;
                                                if (is_array($paradasHist) && !empty($paradasHist)):
                                                    $countEscalas = count($paradasHist);
                                                ?>
                                                    <div class="itinerario-badge-wrap">
                                                        <span class="itinerario-stops-pill" title="<?php 
                                                            $nombres = array_map(function($p) { return $p['ubicacion'] ?? ''; }, $paradasHist);
                                                            echo htmlspecialchars('Escalas: ' . implode(' → ', $nombres), ENT_QUOTES, 'UTF-8');
                                                        ?>">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
                                                            +<?php echo $countEscalas; ?> escala<?php echo $countEscalas > 1 ? 's' : ''; ?>
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                            </td>

                                            <!-- Fechas -->
                                            <td class="dashboard-td font-monospace">
                                                <?php echo $fechaReq . ($horaReq ? " {$horaReq} hrs" : ''); ?>
                                            </td>

                                            <!-- Vehículo -->
                                            <td class="dashboard-td">
                                                <?php if (!empty($vehPlacas)): ?>
                                                    <span class="dashboard-plate-badge font-monospace">
                                                        <?php echo $vehPlacas; ?>
                                                    </span>
                                                    <?php if (!empty($vehEco)): ?>
                                                        <small class="dashboard-cell-sub">Eco #<?php echo $vehEco; ?></small>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="dashboard-cell-muted">Sin unidad asignada</span>
                                                <?php endif; ?>
                                            </td>

                                            <!-- Estatus -->
                                            <td class="dashboard-td">
                                                <span class="badge <?php echo $badgeEstadoClass; ?>">
                                                    <?php echo htmlspecialchars($estado, ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                            </td>

                                            <!-- Dictamen / Observaciones -->
                                            <td class="dashboard-td">
                                                <?php if (!empty($comentarios)): ?>
                                                    <div class="dashboard-cell-sub font-italic">
                                                        "<?php echo $comentarios; ?>"
                                                    </div>
                                                <?php endif; ?>
                                                <?php if (!empty($jefe)): ?>
                                                    <small class="dashboard-cell-muted">
                                                        Dictaminado por: <?php echo $jefe; ?>
                                                    </small>
                                                <?php elseif (empty($comentarios)): ?>
                                                    <span class="dashboard-cell-muted">—</span>
                                                <?php endif; ?>
                                            </td>

                                            <!-- Acciones (Ver Detalles) -->
                                            <td class="dashboard-td text-center">
                                                <button type="button" 
                                                        class="btn-trigger-detalle-historico" 
                                                        title="Ver detalles completos de la comisión"
                                                        data-id="<?php echo $idHist; ?>"
                                                        data-solicitante="<?php echo $solicitante; ?>"
                                                        data-area="<?php echo $area; ?>"
                                                        data-destino="<?php echo $destino; ?>"
                                                        data-tipo="<?php echo htmlspecialchars($h['tipo_comision'] ?? 'Local', ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-fecha-salida="<?php echo $fechaReq . ($horaReq ? " {$horaReq} hrs" : ''); ?>"
                                                        data-fecha-retorno="<?php echo !empty($h['fecha_retorno']) ? date('d/m/Y', strtotime($h['fecha_retorno'])) . (!empty($h['hora_retorno']) ? ' ' . substr($h['hora_retorno'], 0, 5) . ' hrs' : '') : '—'; ?>"
                                                        data-motivo="<?php echo $motivo . (!empty($h['especificacion_motivo']) ? ' — ' . htmlspecialchars($h['especificacion_motivo'], ENT_QUOTES, 'UTF-8') : ''); ?>"
                                                        data-pasajeros="<?php echo (int)($h['num_pasajeros'] ?? 1); ?>"
                                                        data-itinerario="<?php echo htmlspecialchars($itinerarioHistRaw, ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-vehiculo-eco="<?php echo $vehEco; ?>"
                                                        data-vehiculo-placas="<?php echo $vehPlacas; ?>"
                                                        data-vehiculo-modelo="<?php echo htmlspecialchars(trim(($h['vehiculo_marca'] ?? '') . ' ' . ($h['vehiculo_modelo'] ?? '')), ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-km-inicial="<?php echo $h['km_inicial'] !== null ? number_format((float)$h['km_inicial']) . ' km' : '—'; ?>"
                                                        data-km-final="<?php echo $h['km_final'] !== null ? number_format((float)$h['km_final']) . ' km' : '—'; ?>"
                                                        data-km-recorridos="<?php echo $h['km_recorridos'] !== null ? number_format((float)$h['km_recorridos']) . ' km' : '—'; ?>"
                                                        data-estado="<?php echo htmlspecialchars($estado, ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-jefe="<?php echo $jefe ?: 'Administración'; ?>"
                                                        data-fecha-eval="<?php echo !empty($h['fecha_autorizacion']) ? date('d/m/Y H:i', strtotime($h['fecha_autorizacion'])) : '—'; ?>"
                                                        data-comentarios="<?php echo $comentarios ?: 'Sin observaciones registradas'; ?>">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                                    Ver Detalles
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="dashboard-empty-state">
                                            <svg class="dashboard-empty-icon" xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#CBD5E1" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                                            <div class="dashboard-empty-title">No hay registros históricos finalizados aún</div>
                                            <div class="dashboard-empty-desc">Las comisiones concluidas, rechazadas y canceladas se registrarán aquí.</div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Modal 1: Evaluar y Asignar Vehículo (Aprobación) -->
<div id="modalEvaluarAsignar" class="modal-overlay" aria-hidden="true" role="dialog">
    <div class="modal-dialog">
        <div class="modal-header">
            <h4 class="modal-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                Evaluación y Asignación Vehicular
            </h4>
            <button type="button" class="modal-close" data-close-modal title="Cerrar ventana">&times;</button>
        </div>

        <form action="index.php?action=solicitudes_aprobar" method="POST" id="formEvaluarAsignar">
            <?php echo Csrf::renderField(); ?>
            <input type="hidden" name="id_solicitud" id="eval_id_solicitud">

            <div class="modal-body">
                <!-- Ficha Informativa de la Solicitud -->
                <div class="eval-card-grid">
                    <div class="eval-card-item">
                        <div class="eval-card-label">Solicitante / Conductor</div>
                        <div class="eval-card-val" id="eval_resumen_solicitante">--</div>
                        <div class="eval-card-val--muted" id="eval_resumen_area">--</div>
                    </div>

                    <div class="eval-card-item">
                        <div class="eval-card-label">Destino y Tipo de Comisión</div>
                        <div class="eval-card-val" id="eval_resumen_destino">--</div>
                        <div class="eval-card-val--muted" id="eval_resumen_tipo">--</div>
                    </div>

                    <div class="eval-card-item">
                        <div class="eval-card-label">Horarios Solicitados</div>
                        <div class="eval-card-val font-monospace" id="eval_resumen_salida">--</div>
                        <div class="eval-card-val--muted font-monospace" id="eval_resumen_retorno">--</div>
                    </div>

                    <div class="eval-card-item">
                        <div class="eval-card-label">Licencia de Conducir</div>
                        <div class="eval-card-val" id="eval_resumen_licencia_num">--</div>
                        <div class="eval-card-val--muted" id="eval_resumen_licencia_estado">--</div>
                    </div>

                    <div class="eval-card-item eval-card-item--full">
                        <div class="eval-card-label">Motivo de la Comisión & Pasajeros</div>
                        <div class="eval-card-val" id="eval_resumen_motivo">--</div>
                        <div class="eval-card-val--muted" id="eval_resumen_pasajeros">--</div>
                    </div>
                </div>

                <!-- Desglose de Itinerario de Paradas Intermedias -->
                <div class="itinerario-modal-box form-group-hidden mb-14" id="eval_wrap_itinerario">
                    <div class="itinerario-modal-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
                        Itinerario de Escalas y Paradas Intermedias
                    </div>
                    <ul class="itinerario-modal-list" id="eval_lista_itinerario">
                        <!-- Generado dinámicamente por main.js -->
                    </ul>
                </div>

                <?php if (empty($vehiculosDisponibles)): ?>
                    <!-- Alerta de Bloqueo por falta de unidades -->
                    <div class="alert alert--danger mb-14">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <div>
                            <strong>Sin unidades disponibles:</strong> Actualmente no hay vehículos con estatus 'Disponible' en el parque vehicular para asignar a esta comisión. Puede rechazar la solicitud o esperar el retorno de unidades en tránsito.
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Selector Obligatorio de Vehículo Disponible -->
                <div class="mb-14">
                    <label for="eval_id_vehiculo" class="modal-input-label">
                        Vehículo Asignado para la Comisión <span class="text-danger">*</span>
                    </label>
                    <select name="id_vehiculo" id="eval_id_vehiculo" class="modal-select" required <?php echo empty($vehiculosDisponibles) ? 'disabled' : ''; ?>>
                        <option value="">-- Seleccione una unidad disponible --</option>
                        <?php foreach ($vehiculosDisponibles as $v): ?>
                            <option value="<?php echo (int)$v['id_vehiculo']; ?>">
                                Eco #<?php echo htmlspecialchars($v['numero_economico'] ?? 'S/N', ENT_QUOTES, 'UTF-8'); ?> — <?php echo htmlspecialchars(($v['marca'] ?? '') . ' ' . ($v['modelo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?> (Placas: <?php echo htmlspecialchars($v['placas'] ?? 'N/D', ENT_QUOTES, 'UTF-8'); ?>) · <?php echo number_format((float)($v['km_actual'] ?? 0)); ?> km
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Observaciones Administrativas -->
                <div>
                    <label for="eval_comentarios" class="modal-input-label">
                        Observaciones o Instrucciones Administrativas <span class="modal-input-label-hint">(Opcional)</span>
                    </label>
                    <textarea name="comentarios_jefe" id="eval_comentarios" class="modal-input" rows="2" placeholder="Indicaciones de combustible, kilometraje inicial o condiciones particulares..."></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" data-close-modal>Cancelar</button>
                <button type="submit" class="btn-modal-submit btn-modal-submit--success" id="btnConfirmarAprobacion" <?php echo empty($vehiculosDisponibles) ? 'disabled' : ''; ?>>
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Autorizar con Unidad Asignada
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Rechazar Solicitud de Comisión -->
<div id="modalRechazarComision" class="modal-overlay" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-dialog--sm">
        <div class="modal-header">
            <h4 class="modal-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                Rechazar Solicitud de Comisión
            </h4>
            <button type="button" class="modal-close" data-close-modal title="Cerrar ventana">&times;</button>
        </div>

        <form action="index.php?action=solicitudes_rechazar" method="POST" id="formRechazarComision">
            <?php echo Csrf::renderField(); ?>
            <input type="hidden" name="id_solicitud" id="rechazar_id_solicitud">

            <div class="modal-body">
                <p class="modal-desc-tight">
                    ¿Desea rechazar la solicitud <strong id="rechazar_folio_texto"></strong> del servidor público <strong id="rechazar_solicitante_texto"></strong>?
                </p>

                <div class="mb-12">
                    <label for="rechazar_motivo" class="modal-input-label">
                        Motivo o Justificación del Rechazo <span class="text-danger">*</span>
                    </label>
                    <textarea name="comentarios_jefe" id="rechazar_motivo" class="modal-input" rows="3" required placeholder="Indique la justificación (ej. no hay unidades disponibles, fechas incompatibles, comisión improcedente)..."></textarea>
                </div>

                <p class="modal-note">
                    Esta acción quedará registrada en la bitácora de auditoría institucional.
                </p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" data-close-modal>Cancelar</button>
                <button type="submit" class="btn-modal-submit btn-modal-submit--danger">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    Confirmar Rechazo
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 3: Detalle Completo de Solicitud Histórica (Solo Lectura) -->
<div id="modalDetalleSolicitud" class="modal-overlay" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-dialog--lg">
        <div class="modal-header">
            <h4 class="modal-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                Detalle de Comisión — Folio <span id="det_folio_titulo"></span>
            </h4>
            <button type="button" class="modal-close" data-close-modal title="Cerrar ventana">&times;</button>
        </div>

        <div class="modal-body">
            <!-- Sección 1: Datos de la Comisión -->
            <div class="detalle-section-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Información de la Comisión
            </div>
            <div class="eval-card-grid">
                <div class="eval-card-item">
                    <div class="eval-card-label">Solicitante / Conductor</div>
                    <div class="eval-card-val" id="det_solicitante">--</div>
                    <div class="eval-card-val--muted" id="det_area">--</div>
                </div>

                <div class="eval-card-item">
                    <div class="eval-card-label">Destino y Tipo de Comisión</div>
                    <div class="eval-card-val" id="det_destino">--</div>
                    <div class="eval-card-val--muted" id="det_tipo">--</div>
                </div>

                <div class="eval-card-item">
                    <div class="eval-card-label">Horario de Salida</div>
                    <div class="eval-card-val font-monospace" id="det_fecha_salida">--</div>
                </div>

                <div class="eval-card-item">
                    <div class="eval-card-label">Horario de Retorno</div>
                    <div class="eval-card-val font-monospace" id="det_fecha_retorno">--</div>
                </div>

                <div class="eval-card-item eval-card-item--full">
                    <div class="eval-card-label">Motivo de la Comisión &amp; Pasajeros</div>
                    <div class="eval-card-val" id="det_motivo">--</div>
                    <div class="eval-card-val--muted" id="det_pasajeros">--</div>
                </div>
            </div>

            <!-- Sección 2: Itinerario de Paradas Intermedias -->
            <div class="itinerario-modal-box form-group-hidden mb-14" id="det_wrap_itinerario">
                <div class="itinerario-modal-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
                    Itinerario Oficial y Escalas Autorizadas
                </div>
                <ul class="itinerario-modal-list" id="det_lista_itinerario">
                    <!-- Generado dinámicamente por main.js -->
                </ul>
            </div>

            <!-- Sección 3: Vehículo y Odómetro -->
            <div class="detalle-section-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                Unidad Asignada y Lecturas de Odómetro
            </div>
            <div class="eval-card-grid">
                <div class="eval-card-item">
                    <div class="eval-card-label">Vehículo Asignado</div>
                    <div class="eval-card-val" id="det_vehiculo_info">--</div>
                    <div class="eval-card-val--muted" id="det_vehiculo_modelo">--</div>
                </div>

                <div class="eval-card-item">
                    <div class="eval-card-label">Lecturas de Odómetro (Caseta)</div>
                    <div class="eval-card-val font-monospace" id="det_km_inicial">--</div>
                    <div class="eval-card-val--muted font-monospace" id="det_km_final">--</div>
                </div>

                <div class="eval-card-item eval-card-item--full">
                    <div class="eval-card-label">Kilómetros Recorridos en Comisión</div>
                    <div class="eval-card-val font-monospace" id="det_km_recorridos">--</div>
                </div>
            </div>

            <!-- Sección 4: Decisión y Dictamen Final -->
            <div class="detalle-section-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                Decisión y Dictamen Final
            </div>
            <div class="eval-card-grid">
                <div class="eval-card-item">
                    <div class="eval-card-label">Estatus Final</div>
                    <div id="det_estado_wrap">
                        <span class="badge" id="det_estado_badge">--</span>
                    </div>
                </div>

                <div class="eval-card-item">
                    <div class="eval-card-label">Evaluado por</div>
                    <div class="eval-card-val" id="det_jefe">--</div>
                    <div class="eval-card-val--muted font-monospace" id="det_fecha_eval">--</div>
                </div>

                <div class="eval-card-item eval-card-item--full">
                    <div class="eval-card-label">Observaciones / Comentarios del Dictamen</div>
                    <div class="eval-card-val det-comentarios-block" id="det_comentarios">--</div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn-modal-cancel" data-close-modal>Cerrar</button>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
