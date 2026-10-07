<?php
require_once __DIR__ . '/../../../includes/header.php';
require_once __DIR__ . '/../../../includes/sidebar.php';
?>

<main class="app-main">
    <header class="app-topbar">
        <div class="topbar-title">Administración del Sistema — Usuarios</div>
        <div class="topbar-right">
            <span class="topbar-date"><?php echo date('d/m/Y H:i:s'); ?></span>
        </div>
    </header>

    <div class="app-content">
        <!-- Alertas de Retroalimentación -->
        <?php require_once __DIR__ . '/../layouts/alertas.php'; ?>

        <div class="content-card">
            <div class="card-header">
                <h3>Directorio de Usuarios Institucionales (SECOTED)</h3>
                <!-- Botón idéntico en dimensiones y estilo al de Vehículos -->
                <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#modalNuevoUsuario">
                    + Registrar Usuario
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover modern-table">
                        <thead>
                            <tr>
                                <th>Nombre Completo</th>
                                <th>Correo Electrónico</th>
                                <th>Rol en el Sistema</th>
                                <th>Área / Dirección Adscrita</th>
                                <th class="table-cell-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($usuarios)): ?>
                                <?php foreach ($usuarios as $u): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($u['nombre_completo'] ?? '', ENT_QUOTES, 'UTF-8'); ?></strong>
                                            <?php if ((int)($u['id_usuario'] ?? 0) === (int)($_SESSION['id_usuario'] ?? 0)): ?>
                                                <span class="badge badge--info badge-account">Tu cuenta</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <code><?php echo htmlspecialchars($u['correo'] ?? '', ENT_QUOTES, 'UTF-8'); ?></code>
                                        </td>
                                        <td>
                                            <?php 
                                                $idRol = (int)($u['id_rol'] ?? 0);
                                                $claseBadge = match($idRol) {
                                                    1 => 'badge--danger',  // Administrador
                                                    2 => 'badge--info',    // Encargado de control vehicular
                                                    3 => 'badge--warning', // Solicitante
                                                    4 => 'badge--success', // Jefe de área
                                                    default => 'badge--info',
                                                };
                                            ?>
                                            <span class="badge <?php echo $claseBadge; ?>">
                                                <?php echo htmlspecialchars($u['nombre_rol'] ?? 'Sin rol', ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($u['nombre_area'] ?? 'Sin área asignada', ENT_QUOTES, 'UTF-8'); ?>
                                        </td>
                                        <td class="table-cell-end">
                                            <div class="table-actions table-actions--end">
                                                <!-- Botón Editar -->
                                                <button type="button" 
                                                        class="btn-action btn-action--edit btn-trigger-edit-usr"
                                                        data-id="<?php echo (int)$u['id_usuario']; ?>"
                                                        data-nombre="<?php echo htmlspecialchars($u['nombre_completo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-correo="<?php echo htmlspecialchars($u['correo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-rol="<?php echo (int)($u['id_rol'] ?? 0); ?>"
                                                        data-area="<?php echo (int)($u['id_area'] ?? 0); ?>"
                                                        title="Editar datos del usuario">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                    <span>Editar</span>
                                                </button>

                                                <!-- Botón Borrado Lógico (Deshabilitado si es su propia cuenta) -->
                                                <?php if ((int)$u['id_usuario'] !== (int)($_SESSION['id_usuario'] ?? 0)): ?>
                                                    <button type="button" 
                                                            class="btn-action btn-action--delete btn-trigger-delete-usr"
                                                            data-id="<?php echo (int)$u['id_usuario']; ?>"
                                                            data-nombre="<?php echo htmlspecialchars($u['nombre_completo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                            title="Dar de baja usuario (borrado lógico)">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                                        <span>Baja</span>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="table-empty-state">
                                        No se encontraron usuarios registrados activos.
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

<!-- Modal 1: Registrar Nuevo Usuario -->
<div class="modal fade" id="modalNuevoUsuario" tabindex="-1" aria-labelledby="modalNuevoUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNuevoUsuarioLabel">
                    <svg class="modal-header-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                    Registrar Nuevo Usuario
                </h5>
                <button type="button" class="modal-close" data-bs-dismiss="modal" data-close-modal aria-label="Cerrar">&times;</button>
            </div>
            <form action="index.php?action=usuario_crear" method="POST">
                <?php echo Csrf::renderField(); ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nombre_completo" class="modal-input-label">Nombre Completo <span>*</span></label>
                        <input type="text" class="form-control" id="nombre_completo" name="nombre_completo" required maxlength="100" placeholder="Ej. Lic. Roberto Morales Quiñones">
                    </div>
                    <div class="mb-3">
                        <label for="correo" class="modal-input-label">Correo Electrónico Oficial <span>*</span></label>
                        <input type="email" class="form-control" id="correo" name="correo" required maxlength="100" placeholder="ejemplo@durango.gob.mx">
                    </div>
                    <div class="form-grid-2">
                        <div class="mb-3">
                            <label for="id_rol" class="modal-input-label">Rol en el Sistema <span>*</span></label>
                            <select class="form-control modal-select" id="id_rol" name="id_rol" required>
                                <option value="">-- Seleccionar Rol --</option>
                                <?php if (!empty($roles)): ?>
                                    <?php foreach ($roles as $r): ?>
                                        <option value="<?php echo (int)$r['id_rol']; ?>">
                                            <?php echo htmlspecialchars($r['nombre_rol'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="id_area" class="modal-input-label">Área / Dirección</label>
                            <select class="form-control modal-select" id="id_area" name="id_area">
                                <option value="">-- Sin área asignada --</option>
                                <?php if (!empty($areas)): ?>
                                    <?php foreach ($areas as $a): ?>
                                        <option value="<?php echo (int)$a['id_area']; ?>">
                                            <?php echo htmlspecialchars($a['nombre_area'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="modal-input-label">Contraseña Temporal <span>*</span></label>
                        <input type="password" class="form-control" id="password" name="password" required minlength="6" placeholder="Mínimo 6 caracteres">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-modal-cancel" data-bs-dismiss="modal" data-close-modal>Cancelar</button>
                    <button type="submit" class="btn-modal-submit">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Guardar Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Editar Usuario Existente -->
<div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-labelledby="modalEditarUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarUsuarioLabel">
                    <svg class="modal-header-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Editar Usuario Institucional
                </h5>
                <button type="button" class="modal-close" data-bs-dismiss="modal" data-close-modal aria-label="Cerrar">&times;</button>
            </div>
            <form action="index.php?action=usuario_editar" method="POST">
                <?php echo Csrf::renderField(); ?>
                <input type="hidden" name="id_usuario" id="edit_id_usuario">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_nombre_completo" class="modal-input-label">Nombre Completo <span>*</span></label>
                        <input type="text" class="form-control" id="edit_nombre_completo" name="nombre_completo" required maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label for="edit_correo" class="modal-input-label">Correo Electrónico Oficial <span>*</span></label>
                        <input type="email" class="form-control" id="edit_correo" name="correo" required maxlength="100">
                    </div>
                    <div class="form-grid-2">
                        <div class="mb-3">
                            <label for="edit_id_rol" class="modal-input-label">Rol en el Sistema <span>*</span></label>
                            <select class="form-control modal-select" id="edit_id_rol" name="id_rol" required>
                                <option value="">-- Seleccionar Rol --</option>
                                <?php if (!empty($roles)): ?>
                                    <?php foreach ($roles as $r): ?>
                                        <option value="<?php echo (int)$r['id_rol']; ?>">
                                            <?php echo htmlspecialchars($r['nombre_rol'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_id_area" class="modal-input-label">Área / Dirección</label>
                            <select class="form-control modal-select" id="edit_id_area" name="id_area">
                                <option value="">-- Sin área asignada --</option>
                                <?php if (!empty($areas)): ?>
                                    <?php foreach ($areas as $a): ?>
                                        <option value="<?php echo (int)$a['id_area']; ?>">
                                            <?php echo htmlspecialchars($a['nombre_area'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_password" class="modal-input-label">Nueva Contraseña <small class="modal-input-label-hint">(Dejar en blanco para conservar la actual)</small></label>
                        <input type="password" class="form-control" id="edit_password" name="password" minlength="6" placeholder="Opcional: cambiar contraseña">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-modal-cancel" data-bs-dismiss="modal" data-close-modal>Cancelar</button>
                    <button type="submit" class="btn-modal-submit">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Actualizar Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 3: Confirmación de Borrado Lógico (Baja) -->
<div class="modal fade" id="modalEliminarUsuario" tabindex="-1" aria-labelledby="modalEliminarUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog--sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEliminarUsuarioLabel">
                    <svg class="modal-header-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    Confirmar Baja de Usuario
                </h5>
                <button type="button" class="modal-close" data-bs-dismiss="modal" data-close-modal aria-label="Cerrar">&times;</button>
            </div>
            <form action="index.php?action=usuario_eliminar" method="POST" id="formEliminarUsuario">
                <?php echo Csrf::renderField(); ?>
                <input type="hidden" name="id_usuario" id="delete_id_usuario">
                <div class="modal-body">
                    <p class="modal-desc">
                        ¿Está seguro de que desea dar de baja al siguiente usuario institucional?
                    </p>
                    <div class="modal-info-box">
                        <strong id="delete_nombre_usuario">—</strong>
                    </div>
                    <p class="modal-note">
                        <em>Nota: Se aplicará una baja lógica (estatus = 0). El usuario perderá acceso al sistema pero su historial y bitácora se conservarán íntegros.</em>
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-modal-cancel" data-bs-dismiss="modal" data-close-modal>Cancelar</button>
                    <button type="submit" id="btnConfirmarBaja" class="btn-modal-delete">
                        Confirmar Baja
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Abrir modal de Edición con datos precargados
    const modalEditar = document.getElementById('modalEditarUsuario');
    document.querySelectorAll('.btn-trigger-edit-usr').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('edit_id_usuario').value = this.getAttribute('data-id');
            document.getElementById('edit_nombre_completo').value = this.getAttribute('data-nombre');
            document.getElementById('edit_correo').value = this.getAttribute('data-correo');
            document.getElementById('edit_id_rol').value = this.getAttribute('data-rol');
            document.getElementById('edit_id_area').value = this.getAttribute('data-area') || '';
            document.getElementById('edit_password').value = '';

            if (modalEditar) {
                modalEditar.classList.add('active', 'show');
                modalEditar.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            }
        });
    });

    // Abrir modal de Baja Lógica con confirmación
    const modalEliminar = document.getElementById('modalEliminarUsuario');
    const btnConfirmarBaja = document.getElementById('btnConfirmarBaja');
    document.querySelectorAll('.btn-trigger-delete-usr').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const nombre = this.getAttribute('data-nombre');

            document.getElementById('delete_nombre_usuario').textContent = nombre;
            const inputId = document.getElementById('delete_id_usuario');
            if (inputId) {
                inputId.value = id;
            }

            if (modalEliminar) {
                modalEliminar.classList.add('active', 'show');
                modalEliminar.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
