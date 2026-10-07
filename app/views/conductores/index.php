<?php
require_once __DIR__ . '/../../../includes/header.php';
require_once __DIR__ . '/../../../includes/sidebar.php';
?>

<main class="app-main dashboard-main">
    <header class="app-topbar dashboard-topbar">
        <div>
            <h1 class="dashboard-title">Padrón Oficial de Conductores</h1>
            <p class="dashboard-subtitle">SECOTED Durango — Directorio de Servidores Públicos con Licencia de Conducir Registrada</p>
        </div>
        <div class="topbar-right dashboard-topbar-right">
            <span class="topbar-date"><?php echo date('d/m/Y H:i:s'); ?></span>
        </div>
    </header>

    <div class="app-content dashboard-content">
        <?php require_once __DIR__ . '/../layouts/alertas.php'; ?>

        <div class="content-card">
            <div class="card-header">
                <div>
                    <h3 class="dashboard-table-title">Personal Autorizado para Manejo de Unidades</h3>
                    <span class="dashboard-table-desc">Usuarios del sistema con datos vigentes de licencia de manejo</span>
                </div>
                <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#modalNuevoConductor">
                    + Incorporar Conductor
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Servidor Público</th>
                                <th>Área / Cargo</th>
                                <th>No. Licencia</th>
                                <th>Vigencia</th>
                                <th>Estatus Licencia</th>
                                <th>Documento</th>
                                <th class="table-cell-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($conductores)): ?>
                                <?php foreach ($conductores as $c): ?>
                                    <?php 
                                        $vigencia = $c['vigencia_licencia'] ?? '';
                                        $vencida = $vigencia && strtotime($vigencia) < time();
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="dashboard-cell-title">
                                                <strong><?php echo htmlspecialchars($c['nombre_completo'] ?? '', ENT_QUOTES, 'UTF-8'); ?></strong>
                                            </div>
                                            <small class="dashboard-cell-muted">
                                                <?php echo htmlspecialchars($c['correo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="dashboard-cell-secondary">
                                                <?php echo htmlspecialchars($c['nombre_area'] ?? 'Sin área asignada', ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                            <small class="dashboard-cell-muted">
                                                <?php echo htmlspecialchars($c['nombre_rol'] ?? 'Usuario', ENT_QUOTES, 'UTF-8'); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <span class="font-monospace dashboard-plate-badge">
                                                <?php echo htmlspecialchars($c['numero_licencia'] ?? 'S/N', ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="font-monospace <?php echo $vencida ? 'text-vencida' : ''; ?>">
                                                <?php echo htmlspecialchars(!empty($vigencia) ? date('d/m/Y', strtotime($vigencia)) : 'No especificada', ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($vencida): ?>
                                                <span class="kpi-chip kpi-chip--danger">
                                                    <span class="status-dot-amber"></span>
                                                    Vencida
                                                </span>
                                            <?php else: ?>
                                                <span class="kpi-chip kpi-chip--success">
                                                    <span class="status-dot-green"></span>
                                                    Vigente
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($c['foto_licencia'])): ?>
                                                <a href="<?php echo htmlspecialchars($c['foto_licencia'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" class="kpi-chip kpi-chip--info">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                                                    Ver Licencia
                                                </a>
                                            <?php else: ?>
                                                <span class="dashboard-cell-muted">No adjunta</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="table-cell-end">
                                            <div class="table-actions table-actions--end">
                                                <button type="button" 
                                                        class="btn-action btn-action--edit btn-trigger-edit-conductor"
                                                        data-id="<?php echo (int)$c['id_usuario']; ?>"
                                                        data-nombre="<?php echo htmlspecialchars($c['nombre_completo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-licencia="<?php echo htmlspecialchars($c['numero_licencia'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-vigencia="<?php echo htmlspecialchars($c['vigencia_licencia'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-foto="<?php echo htmlspecialchars($c['foto_licencia'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                        title="Editar licencia de conducir">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                    <span>Editar</span>
                                                </button>
                                                <button type="button" 
                                                        class="btn-action btn-action--delete btn-trigger-delete-conductor"
                                                        data-id="<?php echo (int)$c['id_usuario']; ?>"
                                                        data-nombre="<?php echo htmlspecialchars($c['nombre_completo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                        title="Retirar conductor del padrón">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                                    <span>Baja</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="table-empty-state">
                                        No se han registrado servidores públicos con licencia de conducir en el sistema.
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

<!-- Modal Incorporar Conductor al Padrón -->
<div id="modalNuevoConductor" class="modal-overlay" aria-hidden="true" role="dialog" aria-labelledby="modalNuevoTitle">
    <div class="modal-dialog">
        <div class="modal-header">
            <h4 class="modal-title" id="modalNuevoTitle">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Incorporar Conductor al Padrón
            </h4>
            <button type="button" class="modal-close" data-close-modal title="Cerrar ventana">&times;</button>
        </div>
        <form action="index.php?action=conductores_store" method="POST" enctype="multipart/form-data">
            <div class="modal-body">
                <div class="form-grid-2">
                    <div class="form-group-full">
                        <label for="nuevo_id_usuario" class="modal-input-label">Servidor Público <span>*</span></label>
                        <select name="id_usuario" id="nuevo_id_usuario" class="modal-select" required>
                            <option value="">-- Seleccionar servidor público institucional --</option>
                            <?php if (!empty($usuariosDisponibles)): ?>
                                <?php foreach ($usuariosDisponibles as $u): ?>
                                    <option value="<?php echo (int)$u['id_usuario']; ?>">
                                        <?php echo htmlspecialchars($u['nombre_completo'] . ' (' . ($u['nombre_area'] ?? 'General') . ')', ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div>
                        <label for="nuevo_numero_licencia" class="modal-input-label">Número de Licencia <span>*</span></label>
                        <input type="text" name="numero_licencia" id="nuevo_numero_licencia" class="modal-input" required maxlength="30" placeholder="Ej. DGO-2024-8844">
                    </div>
                    <div>
                        <label for="nuevo_vigencia_licencia" class="modal-input-label">Vigencia de Licencia</label>
                        <input type="date" name="vigencia_licencia" id="nuevo_vigencia_licencia" class="modal-input">
                    </div>
                    <div class="form-group-full">
                        <label for="nuevo_foto_licencia" class="modal-input-label">Fotografía o Escaneo de Licencia (Opcional)</label>
                        <input type="file" name="foto_licencia" id="nuevo_foto_licencia" class="modal-input" accept="image/*,.pdf">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" data-close-modal>Cancelar</button>
                <button type="submit" class="btn-modal-submit">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Registrar en Padrón
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Editar Licencia de Conductor -->
<div id="modalEditarConductor" class="modal-overlay" aria-hidden="true" role="dialog" aria-labelledby="modalEditTitle">
    <div class="modal-dialog">
        <div class="modal-header">
            <h4 class="modal-title" id="modalEditTitle">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Actualizar Licencia de Conductor
            </h4>
            <button type="button" class="modal-close" data-close-modal title="Cerrar ventana">&times;</button>
        </div>
        <form action="index.php?action=conductores_editar" method="POST" id="formEditarConductor" enctype="multipart/form-data">
            <input type="hidden" name="id_usuario" id="edit_id_usuario">
            <input type="hidden" name="foto_licencia_actual" id="edit_foto_licencia_actual">
            <div class="modal-body">
                <div class="modal-info-box mb-14">
                    <div class="modal-desc-tight"><strong>Conductor:</strong></div>
                    <p class="modal-note" id="edit_nombre_display">—</p>
                </div>
                <div class="form-grid-2">
                    <div class="form-group-full">
                        <label for="edit_numero_licencia" class="modal-input-label">Número de Licencia <span>*</span></label>
                        <input type="text" name="numero_licencia" id="edit_numero_licencia" class="modal-input" required maxlength="30">
                    </div>
                    <div class="form-group-full">
                        <label for="edit_vigencia_licencia" class="modal-input-label">Vigencia de Licencia</label>
                        <input type="date" name="vigencia_licencia" id="edit_vigencia_licencia" class="modal-input">
                    </div>
                    <div class="form-group-full">
                        <label for="edit_foto_licencia" class="modal-input-label">Reemplazar Documento / Foto de Licencia (Opcional)</label>
                        <input type="file" name="foto_licencia" id="edit_foto_licencia" class="modal-input" accept="image/*,.pdf">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" data-close-modal>Cancelar</button>
                <button type="submit" class="btn-modal-submit">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Confirmar Baja de Conductor -->
<div id="modalEliminarConductor" class="modal-overlay" aria-hidden="true" role="dialog" aria-labelledby="modalBajaTitle">
    <div class="modal-dialog modal-dialog--sm">
        <div class="modal-header">
            <h4 class="modal-title modal-title--danger" id="modalBajaTitle">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                Retirar del Padrón de Conductores
            </h4>
            <button type="button" class="modal-close" data-close-modal title="Cerrar ventana">&times;</button>
        </div>
        <form action="index.php?action=conductores_eliminar" method="POST" id="formEliminarConductor">
            <input type="hidden" name="id_usuario" id="delete_id_usuario">
            <div class="modal-body">
                <div class="delete-warning-box">
                    <div class="delete-warning-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    </div>
                    <div class="delete-warning-text">
                        <p>¿Estás seguro de que deseas retirar al siguiente servidor público del padrón de conductores?</p>
                        <strong id="delete_nombre_conductor" class="modal-danger-info"></strong>
                        <small>Esta acción únicamente desvincula los datos de licencia de conducir. La cuenta institucional del usuario no será eliminada.</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" data-close-modal>Cancelar</button>
                <button type="submit" class="btn-modal-delete">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    Sí, retirar del padrón
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
