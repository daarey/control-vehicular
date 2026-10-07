<?php
require_once __DIR__ . '/../../../includes/header.php';
require_once __DIR__ . '/../../../includes/sidebar.php';
?>

<main class="app-main">
    <header class="app-topbar">
        <div class="topbar-title">Gestión de Solicitudes Vehiculares</div>
        <div class="topbar-right">
            <span class="topbar-date"><?php echo date('d/m/Y H:i:s'); ?></span>
        </div>
    </header>

    <div class="app-content">
        <?php require_once __DIR__ . '/../layouts/alertas.php'; ?>

        <div class="content-card">
            <div class="card-header">
                <h3>Historial de Solicitudes</h3>
                <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#modalNuevaSolicitud">
                    + Nueva Solicitud
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Folio</th>
                                <th>Solicitante</th>
                                <th>Destino</th>
                                <th>Motivo</th>
                                <th>Fecha Requerida</th>
                                <th>Vehículo Solicitado</th>
                                <th>Estado</th>
                                <?php if ($puedeAutorizar): ?>
                                    <th>Acciones</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($solicitudes)): ?>
                                <?php foreach ($solicitudes as $s): ?>
                                    <tr>
                                        <td>#<?php echo (int)($s['id_solicitud'] ?? 0); ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($s['solicitante_nombre'] ?? 'Usuario', ENT_QUOTES, 'UTF-8'); ?></strong>
                                            <?php if (!empty($s['solicitante_correo'])): ?>
                                                <small class="text-muted-block"><?php echo htmlspecialchars($s['solicitante_correo'], ENT_QUOTES, 'UTF-8'); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?php echo htmlspecialchars($s['destino'] ?? '', ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                        <td>
                                            <?php echo htmlspecialchars($s['motivo_descripcion'] ?? 'Oficial', ENT_QUOTES, 'UTF-8'); ?>
                                            <?php if (!empty($s['especificacion_motivo'])): ?>
                                                <small class="text-muted-block" title="<?php echo htmlspecialchars($s['especificacion_motivo'], ENT_QUOTES, 'UTF-8'); ?>">
                                                    <em><?php echo htmlspecialchars(mb_strimwidth($s['especificacion_motivo'], 0, 40, '...'), ENT_QUOTES, 'UTF-8'); ?></em>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($s['fecha_requerida'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            <small class="text-muted-block"><?php echo htmlspecialchars($s['hora_requerida'] ?? '', ENT_QUOTES, 'UTF-8'); ?></small>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($s['vehiculo_placas'] ? ($s['vehiculo_placas'] . ' (' . $s['vehiculo_marca'] . ' ' . $s['vehiculo_modelo'] . ')') : 'Cualquiera disponible', ENT_QUOTES, 'UTF-8'); ?>
                                        </td>
                                        <td>
                                            <?php 
                                                $est = $s['estado_solicitud'] ?? 'Pendiente';
                                                $claseBadge = match($est) {
                                                    'Autorizada' => 'badge--success',
                                                    'Pendiente'  => 'badge--warning',
                                                    default      => 'badge--danger',
                                                };
                                            ?>
                                            <span class="badge <?php echo $claseBadge; ?>">
                                                <?php echo htmlspecialchars($est, ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                            <?php if ($est !== 'Pendiente' && !empty($s['jefe_nombre'])): ?>
                                                <small class="text-muted-block">Por: <?php echo htmlspecialchars($s['jefe_nombre'], ENT_QUOTES, 'UTF-8'); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <?php if ($puedeAutorizar): ?>
                                            <td>
                                                <?php if ($est === 'Pendiente'): ?>
                                                    <div class="table-actions">
                                                        <button type="button" 
                                                                class="btn-action btn-action-success btn-trigger-accion-sol"
                                                                data-id="<?php echo (int)$s['id_solicitud']; ?>"
                                                                data-solicitante="<?php echo htmlspecialchars($s['solicitante_nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                                data-destino="<?php echo htmlspecialchars($s['destino'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                                data-tipo="autorizar">
                                                            Autorizar
                                                        </button>
                                                        <button type="button" 
                                                                class="btn-action btn-action-danger btn-trigger-accion-sol"
                                                                data-id="<?php echo (int)$s['id_solicitud']; ?>"
                                                                data-solicitante="<?php echo htmlspecialchars($s['solicitante_nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                                data-destino="<?php echo htmlspecialchars($s['destino'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                                data-tipo="rechazar">
                                                            Rechazar
                                                        </button>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="status-evaluada">Evaluada</span>
                                                <?php endif; ?>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="<?php echo $puedeAutorizar ? '8' : '7'; ?>" class="table-empty-state">
                                        No se encontraron solicitudes registradas en la base de datos.
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

<!-- Modal Nueva Solicitud -->
<div id="modalNuevaSolicitud" class="modal-overlay" aria-hidden="true" role="dialog">
    <div class="modal-dialog">
        <div class="modal-header">
            <h4 class="modal-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                Nueva Solicitud de Vehículo
            </h4>
            <button type="button" class="modal-close" data-close-modal title="Cerrar ventana">&times;</button>
        </div>
        <form action="index.php?action=solicitudes_store" method="POST">
            <div class="modal-body">
                <div class="form-grid-2">
                    <!-- Destino de la comisión -->
                    <div>
                        <label for="sol_destino" class="modal-input-label">Destino / Lugar de la Comisión <span>*</span></label>
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

                    <!-- Fecha y Hora Estimada de Salida -->
                    <div>
                        <label for="sol_fecha_requerida" class="modal-input-label">Fecha Estimada de Salida <span>*</span></label>
                        <input type="date" name="fecha_requerida" id="sol_fecha_requerida" class="modal-input" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div>
                        <label for="sol_hora_requerida" class="modal-input-label">Hora Estimada de Salida <span>*</span></label>
                        <input type="time" name="hora_requerida" id="sol_hora_requerida" class="modal-input" required>
                    </div>

                    <!-- Fecha y Hora Estimada de Retorno -->
                    <div>
                        <label for="sol_fecha_retorno" class="modal-input-label">Fecha Estimada de Retorno <span>*</span></label>
                        <input type="date" name="fecha_retorno" id="sol_fecha_retorno" class="modal-input" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div>
                        <label for="sol_hora_retorno" class="modal-input-label">Hora Estimada de Retorno <span>*</span></label>
                        <input type="time" name="hora_retorno" id="sol_hora_retorno" class="modal-input" required>
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

<?php if ($puedeAutorizar): ?>
<!-- Modal Autorizar / Rechazar Solicitud -->
<div id="modalAccionSolicitud" class="modal-overlay" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-dialog--sm">
        <div class="modal-header">
            <h4 class="modal-title" id="accion_modal_titulo">
                Evaluar Solicitud
            </h4>
            <button type="button" class="modal-close" data-close-modal title="Cerrar ventana">&times;</button>
        </div>
        <form id="formAccionSolicitud" method="POST" action="">
            <input type="hidden" name="id_solicitud" id="accion_id_solicitud">
            <div class="modal-body">
                <p class="modal-desc-tight">
                    ¿Desea <strong id="accion_tipo_texto"></strong> la solicitud <strong id="accion_folio_texto"></strong> del servidor <strong id="accion_solicitante_texto"></strong>?
                </p>

                <!-- Selector Obligatorio al Autorizar -->
                <div id="wrap_vehiculo_asignado" class="mb-14 form-group-hidden">
                    <label for="accion_id_vehiculo" class="modal-input-label">
                        Vehículo Asignado para la Comisión <span class="text-danger">*</span>
                    </label>
                    <select name="id_vehiculo" id="accion_id_vehiculo" class="modal-select">
                        <option value="">-- Seleccione una unidad disponible --</option>
                        <?php if (!empty($vehiculosDisponibles)): ?>
                            <?php foreach ($vehiculosDisponibles as $v): ?>
                                <option value="<?php echo (int)$v['id_vehiculo']; ?>">
                                    Eco #<?php echo htmlspecialchars($v['numero_economico'] ?? 'S/N', ENT_QUOTES, 'UTF-8'); ?> — <?php echo htmlspecialchars(($v['marca'] ?? '') . ' ' . ($v['modelo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?> (Placas: <?php echo htmlspecialchars($v['placas'] ?? 'N/D', ENT_QUOTES, 'UTF-8'); ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div>
                    <label for="comentarios_jefe" class="modal-input-label">Comentarios o Justificación</label>
                    <textarea name="comentarios_jefe" id="comentarios_jefe" class="modal-input" rows="3" placeholder="Observaciones o motivo de la decisión..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" data-close-modal>Cancelar</button>
                <button type="submit" class="btn-modal-submit" id="btnConfirmarAccion">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Confirmar
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
