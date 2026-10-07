<?php
require_once __DIR__ . '/../../../includes/header.php';
require_once __DIR__ . '/../../../includes/sidebar.php';

$puedeEditarEliminar = RoleMiddleware::esAdmin();
?>

<main class="app-main">
    <header class="app-topbar">
        <div class="topbar-title">Parque Vehicular Institucional</div>
        <div class="topbar-right">
            <span class="topbar-date"><?php echo date('d/m/Y H:i:s'); ?></span>
        </div>
    </header>

    <div class="app-content">
        <?php require_once __DIR__ . '/../layouts/alertas.php'; ?>

        <!-- Botones de Filtro por Estado -->
        <div class="mb-3 d-flex justify-content-between">
            <div class="filter-status-group">
                <a href="index.php?controller=vehiculo&action=index" class="btn-filter-status <?php echo empty($estado) ? 'active' : ''; ?>">Todos</a>
                <a href="index.php?controller=vehiculo&action=index&estado=Disponible" class="btn-filter-status <?php echo ($estado ?? '') === 'Disponible' ? 'active' : ''; ?>">Disponibles</a>
                <a href="index.php?controller=vehiculo&action=index&estado=En ruta" class="btn-filter-status <?php echo ($estado ?? '') === 'En ruta' ? 'active' : ''; ?>">En Ruta</a>
                <a href="index.php?controller=vehiculo&action=index&estado=En mantenimiento" class="btn-filter-status <?php echo ($estado ?? '') === 'En mantenimiento' ? 'active' : ''; ?>">En Mantenimiento</a>
            </div>
            
            <?php if ($puedeEditarEliminar): ?>
            <!-- Botón de Registro Activo -->
            <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#modalNuevoVehiculo">
                + Registrar Vehículo
            </button>
            <?php endif; ?>
        </div>

        <div class="content-card">
            <div class="card-body">
                <div class="table-responsive">
                    <!-- Cabeceras de Tabla Ordenables -->
                    <?php 
                        $nuevoDirParam = htmlspecialchars($nuevoDir ?? 'ASC', ENT_QUOTES, 'UTF-8');
                        $estadoParam = htmlspecialchars($estado ?? '', ENT_QUOTES, 'UTF-8');
                    ?>
                    <table class="table table-hover modern-table">
                        <thead>
                            <tr>
                                <th><a href="index.php?controller=vehiculo&action=index&orden=numero_economico&dir=<?= $nuevoDirParam ?>&estado=<?= $estadoParam ?>" class="text-dark text-decoration-none">No. Ecón. ↕</a></th>
                                <th>Placas</th>
                                <th><a href="index.php?controller=vehiculo&action=index&orden=marca&dir=<?= $nuevoDirParam ?>&estado=<?= $estadoParam ?>" class="text-dark text-decoration-none">Marca / Modelo ↕</a></th>
                                <th><a href="index.php?controller=vehiculo&action=index&orden=modelo_anio&dir=<?= $nuevoDirParam ?>&estado=<?= $estadoParam ?>" class="text-dark text-decoration-none">Año ↕</a></th>
                                <th><a href="index.php?controller=vehiculo&action=index&orden=km_actual&dir=<?= $nuevoDirParam ?>&estado=<?= $estadoParam ?>" class="text-dark text-decoration-none">Km Actual ↕</a></th>
                                <th>Resguardante</th>
                                <th>Estado Operativo</th>
                                <?php if ($puedeEditarEliminar): ?>
                                    <th>Acciones</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($vehiculos)): ?>
                                <?php foreach ($vehiculos as $v): ?>
                                    <tr>
                                        <td><strong>#<?php echo htmlspecialchars($v['numero_economico'] ?? 'S/N', ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                        <td><code><?php echo htmlspecialchars($v['placas'] ?? '', ENT_QUOTES, 'UTF-8'); ?></code></td>
                                        <td><?php echo htmlspecialchars(($v['marca'] ?? '') . ' ' . ($v['modelo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string)($v['modelo_anio'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo number_format((float)($v['km_actual'] ?? 0)); ?> km</td>
                                        <td><?php echo htmlspecialchars($v['resguardante_nombre'] ?? 'Sin asignar', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <?php 
                                                $estadoVehiculo = $v['estado_operativo'] ?? 'Disponible';
                                                $claseBadge = match($estadoVehiculo) {
                                                    'Disponible'       => 'badge--success',
                                                    'En ruta'          => 'badge--info',
                                                    'En mantenimiento' => 'badge--warning',
                                                    default            => 'badge--danger',
                                                };
                                            ?>
                                            <span class="badge <?php echo $claseBadge; ?>">
                                                <?php echo htmlspecialchars($estadoVehiculo, ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </td>
                                        <?php if ($puedeEditarEliminar): ?>
                                            <td>
                                                <div class="table-actions">
                                                    <button type="button" 
                                                            class="btn-action btn-action--edit btn-trigger-edit"
                                                            data-id="<?php echo (int)$v['id_vehiculo']; ?>"
                                                            data-economico="<?php echo htmlspecialchars($v['numero_economico'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                            data-placas="<?php echo htmlspecialchars($v['placas'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                            data-marca="<?php echo htmlspecialchars($v['marca'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                            data-modelo="<?php echo htmlspecialchars($v['modelo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                            data-anio="<?php echo htmlspecialchars((string)($v['modelo_anio'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                                            data-patrimonial="<?php echo htmlspecialchars($v['numero_patrimonial'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                            data-serie="<?php echo htmlspecialchars($v['numero_serie'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                            data-km="<?php echo (int)($v['km_actual'] ?? 0); ?>"
                                                            data-resguardante="<?php echo (int)($v['id_resguardante'] ?? 0); ?>"
                                                            data-estado="<?php echo htmlspecialchars($v['estado_operativo'] ?? 'Disponible', ENT_QUOTES, 'UTF-8'); ?>"
                                                            title="Editar información del vehículo">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                        <span>Editar</span>
                                                    </button>
                                                    <button type="button" 
                                                            class="btn-action btn-action--delete btn-trigger-delete"
                                                            data-id="<?php echo (int)$v['id_vehiculo']; ?>"
                                                            data-info="#<?php echo htmlspecialchars(($v['numero_economico'] ?? '') . ' — ' . ($v['marca'] ?? '') . ' ' . ($v['modelo'] ?? '') . ' (' . ($v['placas'] ?? '') . ')', ENT_QUOTES, 'UTF-8'); ?>"
                                                            title="Dar de baja este vehículo">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                                        <span>Eliminar</span>
                                                    </button>
                                                </div>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="<?php echo $puedeEditarEliminar ? '8' : '7'; ?>" class="table-empty-state">
                                        No hay vehículos registrados en la base de datos.
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

<?php if ($puedeEditarEliminar): ?>
<!-- Modal Registrar Nuevo Vehículo -->
<div id="modalNuevoVehiculo" class="modal-overlay" aria-hidden="true" role="dialog">
    <div class="modal-dialog">
        <div class="modal-header">
            <h4 class="modal-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                Registrar Nueva Unidad Vehicular
            </h4>
            <button type="button" class="modal-close" data-close-modal title="Cerrar ventana">&times;</button>
        </div>
        <form action="index.php?controller=vehiculo&action=store" method="POST">
            <div class="modal-body">
                <div class="form-grid-2">
                    <div>
                        <label for="nuevo_numero_economico" class="modal-input-label">No. Económico <span>*</span></label>
                        <input type="text" name="numero_economico" id="nuevo_numero_economico" class="modal-input" required maxlength="10" placeholder="Ej. ECO-101">
                    </div>
                    <div>
                        <label for="nuevo_placas" class="modal-input-label">Placas <span>*</span></label>
                        <input type="text" name="placas" id="nuevo_placas" class="modal-input text-uppercase" required maxlength="20" placeholder="Ej. FGB-1234">
                    </div>
                    <div>
                        <label for="nuevo_marca" class="modal-input-label">Marca <span>*</span></label>
                        <input type="text" name="marca" id="nuevo_marca" class="modal-input" required maxlength="50" placeholder="Ej. TOYOTA">
                    </div>
                    <div>
                        <label for="nuevo_modelo" class="modal-input-label">Modelo <span>*</span></label>
                        <input type="text" name="modelo" id="nuevo_modelo" class="modal-input" required maxlength="50" placeholder="Ej. HILUX">
                    </div>
                    <div>
                        <label for="nuevo_modelo_anio" class="modal-input-label">Año Modelo <span>*</span></label>
                        <input type="number" name="modelo_anio" id="nuevo_modelo_anio" class="modal-input" required min="1990" max="<?php echo date('Y') + 1; ?>" placeholder="Ej. <?php echo date('Y'); ?>">
                    </div>
                    <div>
                        <label for="nuevo_km_actual" class="modal-input-label">Kilometraje Actual (km)</label>
                        <input type="number" name="km_actual" id="nuevo_km_actual" class="modal-input" min="0" value="0" placeholder="0">
                    </div>
                    <div>
                        <label for="nuevo_numero_patrimonial" class="modal-input-label">No. Patrimonial</label>
                        <input type="text" name="numero_patrimonial" id="nuevo_numero_patrimonial" class="modal-input" maxlength="50" placeholder="Ej. PAT-042">
                    </div>
                    <div>
                        <label for="nuevo_numero_serie" class="modal-input-label">Número de Serie (VIN)</label>
                        <input type="text" name="numero_serie" id="nuevo_numero_serie" class="modal-input text-uppercase" maxlength="50" placeholder="17 caracteres">
                    </div>
                    <div class="form-group-full">
                        <label for="nuevo_id_resguardante" class="modal-input-label">Servidor Público Resguardante <span>*</span></label>
                        <select name="id_resguardante" id="nuevo_id_resguardante" class="modal-select" required>
                            <option value="">-- Seleccionar resguardante --</option>
                            <?php if (!empty($usuarios)): ?>
                                <?php foreach ($usuarios as $u): ?>
                                    <option value="<?php echo (int)$u['id_usuario']; ?>">
                                        <?php echo htmlspecialchars($u['nombre_completo'], ENT_QUOTES, 'UTF-8'); ?> (<?php echo htmlspecialchars($u['correo'], ENT_QUOTES, 'UTF-8'); ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" data-close-modal>Cancelar</button>
                <button type="submit" class="btn-modal-submit">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Guardar Vehículo
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php if ($puedeEditarEliminar): ?>
<!-- Modal de Edición de Vehículo -->
<div id="modalEditarVehiculo" class="modal-overlay" aria-hidden="true" role="dialog">
    <div class="modal-dialog">
        <div class="modal-header">
            <h4 class="modal-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Editar Unidad Vehicular
            </h4>
            <button type="button" class="modal-close" data-close-modal title="Cerrar ventana">&times;</button>
        </div>
        <form action="index.php?action=vehiculos_editar" method="POST" id="formEditarVehiculo">
            <input type="hidden" name="id_vehiculo" id="edit_id_vehiculo">
            <div class="modal-body">
                <div class="form-grid-2">
                    <div>
                        <label for="edit_numero_economico" class="modal-input-label">No. Económico <span>*</span></label>
                        <input type="text" name="numero_economico" id="edit_numero_economico" class="modal-input" required maxlength="10">
                    </div>
                    <div>
                        <label for="edit_placas" class="modal-input-label">Placas <span>*</span></label>
                        <input type="text" name="placas" id="edit_placas" class="modal-input text-uppercase" required maxlength="20">
                    </div>
                    <div>
                        <label for="edit_marca" class="modal-input-label">Marca <span>*</span></label>
                        <input type="text" name="marca" id="edit_marca" class="modal-input" required maxlength="50">
                    </div>
                    <div>
                        <label for="edit_modelo" class="modal-input-label">Modelo <span>*</span></label>
                        <input type="text" name="modelo" id="edit_modelo" class="modal-input" required maxlength="50">
                    </div>
                    <div>
                        <label for="edit_modelo_anio" class="modal-input-label">Año Modelo <span>*</span></label>
                        <input type="number" name="modelo_anio" id="edit_modelo_anio" class="modal-input" required min="1990" max="<?php echo date('Y') + 1; ?>">
                    </div>
                    <div>
                        <label for="edit_km_actual" class="modal-input-label">Kilometraje Actual (km) <span>*</span></label>
                        <input type="number" name="km_actual" id="edit_km_actual" class="modal-input" required min="0">
                    </div>
                    <div>
                        <label for="edit_numero_patrimonial" class="modal-input-label">No. Patrimonial</label>
                        <input type="text" name="numero_patrimonial" id="edit_numero_patrimonial" class="modal-input" maxlength="50">
                    </div>
                    <div>
                        <label for="edit_numero_serie" class="modal-input-label">Número de Serie (VIN)</label>
                        <input type="text" name="numero_serie" id="edit_numero_serie" class="modal-input text-uppercase" maxlength="50">
                    </div>
                    <div class="form-group-full">
                        <label for="edit_id_resguardante" class="modal-input-label">Servidor Público Resguardante</label>
                        <select name="id_resguardante" id="edit_id_resguardante" class="modal-select">
                            <option value="0">-- Sin asignar / Seleccionar resguardante --</option>
                            <?php if (!empty($usuarios)): ?>
                                <?php foreach ($usuarios as $u): ?>
                                    <option value="<?php echo (int)$u['id_usuario']; ?>">
                                        <?php echo htmlspecialchars($u['nombre_completo'], ENT_QUOTES, 'UTF-8'); ?> (<?php echo htmlspecialchars($u['correo'], ENT_QUOTES, 'UTF-8'); ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="form-group-full">
                        <label for="edit_estado_operativo" class="modal-input-label">Estado Operativo <span>*</span></label>
                        <select name="estado_operativo" id="edit_estado_operativo" class="modal-select" required>
                            <option value="Disponible">Disponible</option>
                            <option value="En ruta">En ruta</option>
                            <option value="En mantenimiento">En mantenimiento</option>
                        </select>
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

<!-- Modal de Confirmación de Eliminación (Baja Lógica) -->
<div id="modalEliminarVehiculo" class="modal-overlay" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-dialog--sm">
        <div class="modal-header">
            <h4 class="modal-title modal-title--danger">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                Confirmar Baja de Unidad
            </h4>
            <button type="button" class="modal-close" data-close-modal title="Cerrar ventana">&times;</button>
        </div>
        <form action="index.php?action=vehiculos_eliminar" method="POST" id="formEliminarVehiculo">
            <input type="hidden" name="id_vehiculo" id="delete_id_vehiculo">
            <div class="modal-body">
                <div class="delete-warning-box">
                    <div class="delete-warning-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    </div>
                    <div class="delete-warning-text">
                        <p>¿Estás seguro de que deseas dar de baja la siguiente unidad vehicular?</p>
                        <strong id="delete_vehiculo_info" class="modal-danger-info"></strong>
                        <small>La unidad dejará de estar disponible en el parque vehicular activo, pero su historial de movimientos y solicitudes se mantendrá íntegro en la base de datos.</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" data-close-modal>Cancelar</button>
                <button type="submit" class="btn-modal-delete">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    Sí, dar de baja
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>

