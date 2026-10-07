<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($tituloDocumento ?? 'Bitácora de Auditoría', ENT_QUOTES, 'UTF-8'); ?> — SECOTED</title>
    <style>
        <?php readfile(__DIR__ . '/../../../public/assets/css/pdf_report.css'); ?>
        /* Ajustes específicos de columnas para Bitácora de Auditoría */
        .col-fecha   { width: 12%; }
        .col-usuario { width: 16%; }
        .col-correo  { width: 18%; }
        .col-modulo  { width: 12%; }
        .col-id      { width: 7%; text-align: center; }
        .col-accion  { width: 10%; text-align: center; }
        .col-detalle { width: 25%; }
    </style>
</head>
<body>

    <!-- ═══ Encabezado Institucional Compacto (≤ 2cm) ═══ -->
    <table class="header-table">
        <tr>
            <td class="header-left">
                <div class="header-gov">Gobierno del Estado de Durango &mdash; SECOTED</div>
                <div class="header-secretaria">Secretar&iacute;a de Contralor&iacute;a</div>
                <div class="header-modulo">Unidad de Tecnolog&iacute;as y Seguridad de la Informaci&oacute;n &mdash; Bit&aacute;cora de Auditor&iacute;a</div>
            </td>
            <td class="header-right">
                <table class="meta-table">
                    <tr>
                        <td class="meta-label">Fecha/Hora de emisi&oacute;n:</td>
                        <td class="meta-value"><?php echo date('d/m/Y H:i:s'); ?></td>
                    </tr>
                    <tr>
                        <td class="meta-label">Auditor emisor:</td>
                        <td class="meta-value"><?php echo htmlspecialchars($_SESSION['nombre_completo'] ?? 'Administrador', ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                    <tr>
                        <td class="meta-label">Total de eventos:</td>
                        <td class="meta-value"><?php echo number_format((int)($totalEventos ?? 0)); ?></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- ═══ Barra de Resumen / KPIs ═══ -->
    <table class="stats-table">
        <tr>
            <td>
                <span class="stat-label">Total Eventos</span>
                <span class="stat-val"><?php echo number_format((int)($totalEventos ?? 0)); ?></span>
            </td>
            <td>
                <span class="stat-label">Nuevos Registros</span>
                <span class="stat-val closed"><?php echo number_format((int)($totalCrear ?? 0)); ?></span>
            </td>
            <td>
                <span class="stat-label">Modificaciones</span>
                <span class="stat-val info"><?php echo number_format((int)($totalActualizar ?? 0)); ?></span>
            </td>
            <td>
                <span class="stat-label">Bajas / Eliminaciones</span>
                <span class="stat-val danger"><?php echo number_format((int)($totalEliminar ?? 0)); ?></span>
            </td>
            <td>
                <span class="stat-label">Movimientos Operativos</span>
                <span class="stat-val active"><?php echo number_format((int)($totalSalidasEntradas ?? 0)); ?></span>
            </td>
        </tr>
    </table>

    <!-- ═══ Tabla de Auditoría y Trazabilidad ═══ -->
    <table class="data-table">
        <thead>
            <tr>
                <th class="col-fecha">Fecha y Hora</th>
                <th class="col-usuario">Usuario Autorizado</th>
                <th class="col-correo">Correo Institucional</th>
                <th class="col-modulo">M&oacute;dulo / Tabla</th>
                <th class="col-id">ID Reg.</th>
                <th class="col-accion">Acci&oacute;n</th>
                <th class="col-detalle">Detalle de la Operaci&oacute;n</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($bitacora)): ?>
                <?php foreach ($bitacora as $b): ?>
                    <?php 
                        $acc = strtoupper(trim($b['accion'] ?? ''));
                        $badgeClass = match($acc) {
                            'CREAR'             => 'badge-status-success',
                            'ACTUALIZAR'        => 'badge-status-info',
                            'ELIMINAR', 'BAJA'  => 'badge-status-danger',
                            'SALIDA', 'ENTRADA' => 'badge-status-warning',
                            default             => 'badge-status-info',
                        };
                    ?>
                    <tr>
                        <td>
                            <?php 
                                $fechaEvento = !empty($b['fecha']) ? date('d/m/Y H:i:s', strtotime($b['fecha'])) : '—';
                                echo htmlspecialchars($fechaEvento, ENT_QUOTES, 'UTF-8');
                            ?>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($b['usuario_nombre'] ?? 'Usuario', ENT_QUOTES, 'UTF-8'); ?></strong>
                        </td>
                        <td>
                            <span class="font-mono"><?php echo htmlspecialchars($b['usuario_correo'] ?? 'Sin correo', ENT_QUOTES, 'UTF-8'); ?></span>
                        </td>
                        <td>
                            <span class="badge-status badge-status-info">
                                <?php echo htmlspecialchars($b['tabla_afectada'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </td>
                        <td class="text-center font-mono">
                            #<?php echo (int)($b['id_registro'] ?? 0); ?>
                        </td>
                        <td class="text-center">
                            <span class="badge-status <?php echo $badgeClass; ?>">
                                <?php echo htmlspecialchars($acc, ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($b['detalle'] ?? '—', ENT_QUOTES, 'UTF-8'); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center" style="padding: 12px; color: #64748b; font-style: italic;">
                        No se encontraron eventos de auditor&iacute;a registrados en la bit&aacute;cora institucional.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- ═══ Pie de Página Institucional ═══ -->
    <div class="footer">
        <table class="footer-table">
            <tr>
                <td style="text-align: left; width: 70%;">
                    Sistema Integral de Control Vehicular &bull; Secretar&iacute;a de Contralor&iacute;a del Estado de Durango (SECOTED) &bull; Trazabilidad Digital Inalterable
                </td>
                <td style="text-align: right; width: 30%;">
                    Fecha de emisi&oacute;n: <?php echo date('d/m/Y H:i:s'); ?>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
