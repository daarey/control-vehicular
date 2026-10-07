<?php
require_once __DIR__ . '/../../../includes/header.php';
require_once __DIR__ . '/../../../includes/sidebar.php';
?>

<main class="app-main">
    <header class="app-topbar">
        <div class="topbar-title">Estructura Organizacional — Áreas</div>
        <div class="topbar-right">
            <span class="topbar-date"><?php echo date('d/m/Y H:i:s'); ?></span>
        </div>
    </header>

    <div class="app-content">
        <?php require_once __DIR__ . '/../layouts/alertas.php'; ?>

        <div class="content-card">
            <div class="card-header">
                <h3>Áreas y Direcciones Institucionales (SECOTED)</h3>
                <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#modalNuevaArea">
                    + Nueva Área
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre del Área</th>
                                <th>Estatus</th>
                                <th class="table-cell-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($areas)): ?>
                                <?php foreach ($areas as $a): ?>
                                    <tr>
                                        <td>#<?php echo (int)($a['id_area'] ?? 0); ?></td>
                                        <td><strong><?php echo htmlspecialchars($a['nombre_area'] ?? '', ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                        <td>
                                            <span class="badge <?php echo ($a['estatus'] ?? 1) ? 'badge--success' : 'badge--danger'; ?>">
                                                <?php echo ($a['estatus'] ?? 1) ? 'Activa' : 'Inactiva'; ?>
                                            </span>
                                        </td>
                                        <td class="table-cell-end">
                                            <div class="table-actions table-actions--end">
                                                <button type="button" 
                                                        class="btn-action btn-action--edit btn-trigger-edit-area"
                                                        data-id="<?php echo (int)$a['id_area']; ?>"
                                                        data-nombre="<?php echo htmlspecialchars($a['nombre_area'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                        title="Editar nombre del área">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                    <span>Editar</span>
                                                </button>
                                                <button type="button" 
                                                        class="btn-action btn-action--delete btn-trigger-delete-area"
                                                        data-id="<?php echo (int)$a['id_area']; ?>"
                                                        data-nombre="<?php echo htmlspecialchars($a['nombre_area'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                        title="Eliminar área institucional">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                                    <span>Eliminar</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="table-empty-state">
                                        No se encontraron áreas registradas.
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

<!-- Modal Nueva Área -->
<div id="modalNuevaArea" class="modal-overlay" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-dialog--sm">
        <div class="modal-header">
            <h4 class="modal-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                Nueva Área Institucional
            </h4>
            <button type="button" class="modal-close" data-close-modal title="Cerrar ventana">&times;</button>
        </div>
        <form action="index.php?action=area_crear" method="POST">
            <?php echo Csrf::renderField(); ?>
            <div class="modal-body">
                <div class="mb-16">
                    <label for="nombre_area" class="modal-input-label">Nombre del Área / Dirección <span>*</span></label>
                    <input type="text" name="nombre_area" id="nombre_area" class="modal-input" required maxlength="100" placeholder="Ej. Dirección de Auditoría Gubernamental">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" data-close-modal>Cancelar</button>
                <button type="submit" class="btn-modal-submit">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Guardar Área
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Editar Área -->
<div id="modalEditarArea" class="modal-overlay" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-dialog--sm">
        <div class="modal-header">
            <h4 class="modal-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Editar Área Institucional
            </h4>
            <button type="button" class="modal-close" data-close-modal title="Cerrar ventana">&times;</button>
        </div>
        <form action="index.php?action=area_editar" method="POST" id="formEditarArea">
            <?php echo Csrf::renderField(); ?>
            <input type="hidden" name="id_area" id="edit_id_area">
            <div class="modal-body">
                <div class="mb-16">
                    <label for="edit_nombre_area" class="modal-input-label">Nombre del Área / Dirección <span>*</span></label>
                    <input type="text" name="nombre_area" id="edit_nombre_area" class="modal-input" required maxlength="100">
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

<!-- Modal Confirmar Eliminación de Área -->
<div id="modalEliminarArea" class="modal-overlay" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-dialog--sm">
        <div class="modal-header">
            <h4 class="modal-title modal-title--danger">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                Confirmar Eliminación de Área
            </h4>
            <button type="button" class="modal-close" data-close-modal title="Cerrar ventana">&times;</button>
        </div>
        <form action="index.php?action=area_eliminar" method="POST" id="formEliminarArea">
            <?php echo Csrf::renderField(); ?>
            <input type="hidden" name="id_area" id="delete_id_area">
            <div class="modal-body">
                <div class="delete-warning-box">
                    <div class="delete-warning-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    </div>
                    <div class="delete-warning-text">
                        <p>¿Estás seguro de que deseas eliminar la siguiente área institucional?</p>
                        <strong id="delete_nombre_area" class="modal-danger-info"></strong>
                        <small>Esta acción eliminará permanentemente el área del catálogo. Verifique que no existan conductores o usuarios asignados.</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" data-close-modal>Cancelar</button>
                <button type="submit" class="btn-modal-delete">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    Sí, eliminar
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
