<?php
/**
 * app/views/dashboard/solicitante.php
 * Panel de Control dedicado para el Servidor Público Solicitante (Conductor Responsable).
 * Alineado estrictamente con el sistema de diseño institucional SECOTED.
 * Zero CSS/JS inline: Estilos y comportamiento provistos por dashboard.css y main.js.
 */

require_once __DIR__ . '/../../../includes/header.php';
require_once __DIR__ . '/../../../includes/sidebar.php';

// Variables preparadas por DashboardController::dashboardSolicitante()
$nombreServidor = $usuario['nombre_completo'] ?? $_SESSION['nombre_completo'] ?? 'Servidor Público';
$areaServidor   = $usuario['nombre_area'] ?? 'Área de Adscripción Institucional';
$inicial        = mb_strtoupper(mb_substr($nombreServidor, 0, 1, 'UTF-8'), 'UTF-8');

// Evaluación de vigencia de la licencia
$tieneLicencia    = !empty($licencia['numero_licencia']) && !empty($licencia['vigencia_licencia']);
$esLicenciaVigente= false;
$badgeLicenciaClase = 'badge--warning';
$badgeLicenciaTexto = 'Sin registrar';

if ($tieneLicencia) {
    $fechaVigenciaTs = strtotime($licencia['vigencia_licencia']);
    $hoyTs           = strtotime('today');

    if ($fechaVigenciaTs >= $hoyTs) {
        $esLicenciaVigente  = true;
        $badgeLicenciaClase = 'badge--success';
        $badgeLicenciaTexto = 'Vigente';
    } else {
        $badgeLicenciaClase = 'badge--danger';
        $badgeLicenciaTexto = 'Vencida';
    }
}
?>

<main class="app-main dashboard-main">
    <!-- Barra Superior Institucional -->
    <header class="app-topbar dashboard-topbar">
        <div>
            <h1 class="dashboard-title">
                Portal del Solicitante
            </h1>
            <p class="dashboard-subtitle">
                SECOTED Durango — Comisiones Oficiales y Asignación Vehicular
            </p>
        </div>
        <div class="topbar-right dashboard-topbar-right">
            <span class="topbar-date"><?php echo date('d/m/Y H:i:s'); ?></span>
        </div>
    </header>

    <div class="app-content dashboard-content">
        <!-- Notificaciones de Sesión / Flash Messages -->
        <?php require_once __DIR__ . '/../layouts/alertas.php'; ?>

        <!-- ══════════════════════════════════════════════════════════════════ -->
        <!-- ZONA SUPERIOR: Saludo Institucional + Acreditación de Licencia    -->
        <!-- ══════════════════════════════════════════════════════════════════ -->
        <section class="solicitante-header-card">
            <!-- Saludo y Perfil -->
            <div class="solicitante-profile-wrap">
                <div class="solicitante-avatar-circle" aria-hidden="true">
                    <?php echo htmlspecialchars($inicial, ENT_QUOTES, 'UTF-8'); ?>
                </div>
                <div>
                    <h2 class="solicitante-welcome-title">
                        Bienvenido, <?php echo htmlspecialchars($nombreServidor, ENT_QUOTES, 'UTF-8'); ?>
                    </h2>
                    <div class="solicitante-meta-row">
                        <span class="solicitante-area-tag">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                            <?php echo htmlspecialchars($areaServidor, ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                        <span>·</span>
                        <span>Conductor Responsable</span>
                    </div>
                </div>
            </div>

            <!-- Tarjeta de Licencia de Conducir Oficial -->
            <div class="solicitante-license-card">
                <div class="license-card-header">
                    <span class="license-card-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                        Licencia de Conducir
                    </span>
                    <span class="badge <?php echo $badgeLicenciaClase; ?>">
                        <?php echo $badgeLicenciaTexto; ?>
                    </span>
                </div>
                <div class="license-card-body">
                    <div>
                        <span class="license-num-text font-monospace">
                            <?php echo htmlspecialchars($licencia['numero_licencia'] ?? 'No registrada', ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    </div>
                    <div class="license-exp-text">
                        Vigencia: 
                        <span class="license-exp-date font-monospace">
                            <?php 
                            if (!empty($licencia['vigencia_licencia'])) {
                                echo date('d/m/Y', strtotime($licencia['vigencia_licencia']));
                            } else {
                                echo 'Pendiente';
                            }
                            ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Botón de Acción Principal -->
            <div>
                <button type="button" class="btn btn-dark solicitante-btn-nueva" data-bs-toggle="modal" data-bs-target="#modalNuevaSolicitud">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Nueva Solicitud de Vehículo
                </button>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════ -->
        <!-- ZONA CENTRAL: Banner de Comisión Activa / Vehículo Asignado      -->
        <!-- ══════════════════════════════════════════════════════════════════ -->
        <?php if ($solicitudActiva !== null): ?>
            <section class="active-commission-card">
                <div class="commission-header">
                    <h3 class="commission-header-title">
                        <span class="pulse-indicator" aria-hidden="true"></span>
                        Comisión Activa — Vehículo Asignado
                    </h3>
                    <div>
                        <span class="badge badge--success">
                            Solicitud #<?php echo (int)($solicitudActiva['id_solicitud'] ?? 0); ?> · <?php echo htmlspecialchars($solicitudActiva['estado_solicitud'] ?? 'Autorizada', ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    </div>
                </div>

                <div class="commission-details-grid">
                    <div class="commission-item">
                        <span class="commission-item-label">Vehículo Oficial</span>
                        <div class="commission-item-value">
                            <span class="dashboard-plate-badge font-monospace">
                                <?php echo htmlspecialchars($solicitudActiva['vehiculo_placas'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                            <div>
                                <?php echo htmlspecialchars(($solicitudActiva['vehiculo_marca'] ?? '') . ' ' . ($solicitudActiva['vehiculo_modelo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        </div>
                    </div>

                    <div class="commission-item">
                        <span class="commission-item-label">Destino Programado</span>
                        <div class="commission-item-value">
                            <?php echo htmlspecialchars($solicitudActiva['destino'] ?? 'Comisión Oficial', ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    </div>

                    <div class="commission-item">
                        <span class="commission-item-label">Fecha y Hora Requerida</span>
                        <div class="commission-item-value font-monospace">
                            <?php 
                            $fReq = !empty($solicitudActiva['fecha_requerida']) ? date('d/m/Y', strtotime($solicitudActiva['fecha_requerida'])) : '';
                            $hReq = !empty($solicitudActiva['hora_requerida']) ? substr($solicitudActiva['hora_requerida'], 0, 5) : '';
                            echo htmlspecialchars(trim("{$fReq} {$hReq}"), ENT_QUOTES, 'UTF-8');
                            ?>
                        </div>
                    </div>

                    <div class="commission-item">
                        <span class="commission-item-label">Motivo de la Comisión</span>
                        <div class="commission-item-value">
                            <?php echo htmlspecialchars($solicitudActiva['motivo_descripcion'] ?? 'Oficial', ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- ══════════════════════════════════════════════════════════════════ -->
        <!-- ZONA INFERIOR: Tabla de Mis Solicitudes Recientes                 -->
        <!-- ══════════════════════════════════════════════════════════════════ -->
        <section class="content-card dashboard-table-card">
            <div class="card-header solicitante-table-head">
                <div>
                    <h3 class="dashboard-table-title">Mis Solicitudes Recientes</h3>
                    <span class="dashboard-table-desc">Historial cronológico de trámites de comisiones vehiculares</span>
                </div>
                <div>
                    <span class="solicitante-count-badge">
                        <?php echo count($solicitudesRecientes); ?> registro(s)
                    </span>
                </div>
            </div>

            <div class="card-body dashboard-table-body">
                <div class="table-responsive">
                    <table class="modern-table dashboard-table">
                        <thead>
                            <tr class="dashboard-tr-head">
                                <th class="dashboard-th">Folio</th>
                                <th class="dashboard-th">Fecha Requerida</th>
                                <th class="dashboard-th">Destino</th>
                                <th class="dashboard-th">Motivo</th>
                                <th class="dashboard-th">Vehículo Asignado</th>
                                <th class="dashboard-th">Estatus</th>
                                <th class="dashboard-th dashboard-th-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($solicitudesRecientes)): ?>
                                <?php foreach ($solicitudesRecientes as $sol): ?>
                                    <?php 
                                    $estado = $sol['estado_solicitud'] ?? 'Pendiente';
                                    $badgeClase = match ($estado) {
                                        'Autorizada' => 'badge--success',
                                        'Pendiente'  => 'badge--warning',
                                        'En curso'   => 'badge--info',
                                        'Rechazada'  => 'badge--danger',
                                        'Cancelada'  => 'badge--danger',
                                        default      => 'badge--info'
                                    };
                                    ?>
                                    <tr>
                                        <!-- Folio -->
                                        <td class="dashboard-td font-monospace">
                                            <strong>#<?php echo (int)($sol['id_solicitud'] ?? 0); ?></strong>
                                        </td>

                                        <!-- Fecha -->
                                        <td class="dashboard-td font-monospace">
                                            <?php 
                                            $f = !empty($sol['fecha_requerida']) ? date('d/m/Y', strtotime($sol['fecha_requerida'])) : '—';
                                            $h = !empty($sol['hora_requerida']) ? substr($sol['hora_requerida'], 0, 5) : '';
                                            echo htmlspecialchars(trim("{$f} {$h}"), ENT_QUOTES, 'UTF-8');
                                            ?>
                                        </td>

                                        <!-- Destino -->
                                        <td class="dashboard-td">
                                            <strong><?php echo htmlspecialchars($sol['destino'] ?? '', ENT_QUOTES, 'UTF-8'); ?></strong>
                                            <?php
                                            if (!empty($sol['itinerario_paradas'])) {
                                                $paradasSol = json_decode($sol['itinerario_paradas'], true);
                                                if (is_array($paradasSol) && !empty($paradasSol)) {
                                                    $numParadas = count($paradasSol);
                                                    echo '<div class="itinerario-badge-wrap">';
                                                    echo '<span class="itinerario-stops-pill" title="Escalas intermedias registradas">';
                                                    echo '<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>';
                                                    echo '+' . $numParadas . ' escala' . ($numParadas > 1 ? 's' : '');
                                                    echo '</span>';
                                                    echo '</div>';
                                                }
                                            }
                                            ?>
                                        </td>

                                        <!-- Motivo -->
                                        <td class="dashboard-td">
                                            <div><?php echo htmlspecialchars($sol['motivo_descripcion'] ?? 'Oficial', ENT_QUOTES, 'UTF-8'); ?></div>
                                            <?php if (!empty($sol['especificacion_motivo'])): ?>
                                                <small class="dashboard-cell-muted" title="<?php echo htmlspecialchars($sol['especificacion_motivo'], ENT_QUOTES, 'UTF-8'); ?>">
                                                    <?php echo htmlspecialchars(mb_strimwidth($sol['especificacion_motivo'], 0, 45, '...'), ENT_QUOTES, 'UTF-8'); ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Vehículo Asignado -->
                                        <td class="dashboard-td">
                                            <?php if (!empty($sol['vehiculo_placas'])): ?>
                                                <span class="dashboard-plate-badge font-monospace">
                                                    <?php echo htmlspecialchars($sol['vehiculo_placas'], ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                                <span class="dashboard-cell-secondary">
                                                    <?php echo htmlspecialchars(($sol['vehiculo_marca'] ?? '') . ' ' . ($sol['vehiculo_modelo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="kpi-chip kpi-chip--neutral">
                                                    Pendiente de asignación
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Estatus -->
                                        <td class="dashboard-td">
                                            <span class="badge <?php echo $badgeClase; ?>">
                                                <?php echo htmlspecialchars($estado, ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </td>

                                        <!-- Acciones -->
                                        <td class="dashboard-td-center">
                                            <?php if ($estado === 'Pendiente'): ?>
                                                <form action="index.php?action=solicitudes_cancelar" method="POST" class="form-action-cancel">
                                                    <input type="hidden" name="id_solicitud" value="<?php echo (int)($sol['id_solicitud'] ?? 0); ?>">
                                                    <button type="submit" class="btn-action btn-action--delete" title="Cancelar Solicitud de Comisión">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                                        <span>Cancelar</span>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="dashboard-cell-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="dashboard-empty-state">
                                        <svg class="dashboard-empty-icon" xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                                        <div class="dashboard-empty-title">Sin solicitudes registradas</div>
                                        <p class="dashboard-empty-desc">Haga clic en "+ Nueva Solicitud de Vehículo" para solicitar una unidad oficial.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</main>

<!-- ══════════════════════════════════════════════════════════════════ -->
<!-- MODAL: Nueva Solicitud de Vehículo                                -->
<!-- ══════════════════════════════════════════════════════════════════ -->
<div id="modalNuevaSolicitud" class="modal-overlay" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-content">
        <div class="modal-header">
            <h4 class="modal-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                Nueva Solicitud de Vehículo Oficial
            </h4>
            <button type="button" class="modal-close" data-close-modal title="Cerrar ventana">&times;</button>
        </div>
        <form action="index.php?action=solicitudes_store" method="POST">
            <div class="modal-body">
                <div class="form-grid-2">
                    <!-- Destino de la comisión -->
                    <div>
                        <label for="sol_destino" class="modal-input-label">Destino Final de la Comisión <span>*</span></label>
                        <input type="text" name="destino" id="sol_destino" class="modal-input" required maxlength="150" placeholder="Ej. Gómez Palacio, Dgo.">
                    </div>

                    <!-- Tipo de Comisión (Local / Foránea) -->
                    <div>
                        <label for="sol_tipo_comision" class="modal-input-label">Tipo de Comisión <span>*</span></label>
                        <select name="tipo_comision" id="sol_tipo_comision" class="modal-select" required>
                            <option value="Local">Local (Dentro de la Zona Urbana)</option>
                            <option value="Foránea">Foránea (Fuera de la Zona Urbana / Municipio)</option>
                        </select>
                    </div>

                    <!-- Itinerario y Paradas Intermedias (Opcional) -->
                    <div class="form-group-full paradas-section">
                        <div class="paradas-section-header">
                            <div>
                                <label class="modal-input-label">
                                    Itinerario y Paradas Intermedias <span class="modal-input-label-hint">(Opcional)</span>
                                </label>
                                <span class="paradas-section-desc">
                                    Puntos de escala, dependencias o diligencias previas al destino final
                                </span>
                            </div>
                            <button type="button" class="btn-parada-agregar" id="btn-agregar-parada">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                                + Agregar Parada Intermedia
                            </button>
                        </div>
                        <div id="contenedor-paradas" class="contenedor-paradas">
                            <!-- Filas dinámicas insertadas por main.js -->
                        </div>
                    </div>

                    <!-- Fecha y Hora Estimada de Salida -->
                    <div>
                        <label for="sol_fecha_requerida" class="modal-input-label">Fecha Estimada de Salida <span>*</span></label>
                        <input type="date" name="fecha_requerida" id="sol_fecha_requerida" class="modal-input" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div>
                        <label for="sol_hora_requerida" class="modal-input-label">Hora Estimada de Salida <span>*</span></label>
                        <input type="time" name="hora_requerida" id="sol_hora_requerida" class="modal-input" required>
                    </div>

                    <!-- Fecha y Hora Estimada de Retorno (Opcional) -->
                    <div>
                        <label for="sol_fecha_retorno" class="modal-input-label">Fecha Estimada de Retorno <span class="modal-input-label-hint">(Opcional)</span></label>
                        <input type="date" name="fecha_retorno" id="sol_fecha_retorno" class="modal-input" min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div>
                        <label for="sol_hora_retorno" class="modal-input-label">Hora Estimada de Retorno <span class="modal-input-label-hint">(Opcional)</span></label>
                        <input type="time" name="hora_retorno" id="sol_hora_retorno" class="modal-input">
                    </div>

                    <!-- Motivo de la Comisión -->
                    <div class="form-group-full">
                        <label for="id_motivo" class="modal-input-label">Motivo de la Comisión <span>*</span></label>
                        <select name="id_motivo" id="id_motivo" class="modal-select" required>
                            <option value="">-- Seleccionar Motivo --</option>
                            <?php if (!empty($motivos)): ?>
                                <?php foreach ($motivos as $m): ?>
                                    <option value="<?php echo (int)$m['id_motivo']; ?>" 
                                            data-requiere-detalle="<?php echo ($m['requiere_especificacion'] ?? 0) ? '1' : '0'; ?>">
                                        <?php echo htmlspecialchars($m['descripcion'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Especificación Detallada (Condicional) -->
                    <div class="form-group-full form-group-hidden" id="wrap_especificar_motivo">
                        <label for="especificacion_motivo" class="modal-input-label">Especificación Detallada del Motivo <span>*</span></label>
                        <textarea name="especificacion_motivo" id="especificacion_motivo" class="modal-input" rows="2" placeholder="Detalle las actividades oficiales a realizar..."></textarea>
                    </div>

                    <!-- Número de Pasajeros / Acompañantes -->
                    <div class="form-group-full">
                        <label for="sol_num_pasajeros" class="modal-input-label">Número de Pasajeros / Acompañantes <span>*</span></label>
                        <input type="number" name="num_pasajeros" id="sol_num_pasajeros" class="modal-input" min="1" max="25" value="1" required>
                    </div>

                    <!-- Aviso de Asignación Administrativa (Cero selección vehicular) -->
                    <div class="form-group-full">
                        <div class="modal-info-box">
                            <div class="modal-desc-tight">
                                <strong>Asignación Vehicular Oficial:</strong>
                            </div>
                            <p class="modal-note">
                                La unidad vehicular será asignada por el Departamento de Control Vehicular conforme a la disponibilidad del parque vehicular al autorizar la solicitud.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" data-close-modal>Cancelar</button>
                <button type="submit" class="btn-modal-submit">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Enviar Solicitud
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
