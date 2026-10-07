<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($tituloDocumento ?? 'Bitácora de Movimientos', ENT_QUOTES, 'UTF-8'); ?> — SECOTED</title>
    <style>
        <?php readfile(__DIR__ . '/../../../public/assets/css/pdf_report.css'); ?>
        /* Ajustes específicos de columnas para Bitácora de Movimientos */
        .badge-placas { font-family: monospace; font-size: 7pt; font-weight: bold; color: #0f172a; }
        .text-eco { color: #64748b; font-size: 6.5pt; }
    </style>
</head>
<body>

    <!-- ═══ Encabezado Institucional Compacto (≤ 2cm) ═══ -->
    <table class="header-table">
        <tr>
            <td class="header-left">
                <div class="header-gov">Gobierno del Estado de Durango &mdash; SECOTED</div>
                <div class="header-secretaria">Secretar&iacute;a de Contralor&iacute;a</div>
                <div class="header-modulo">Control de Caseta &mdash; Bit&aacute;cora de Movimientos Vehiculares</div>
            </td>
            <td class="header-right">
                <table class="meta-table">
                    <tr>
                        <td class="meta-label">Fecha/Hora de emisi&oacute;n:</td>
                        <td class="meta-value"><?php echo date('d/m/Y H:i:s'); ?></td>
                    </tr>
                    <tr>
                        <td class="meta-label">Servidor P&uacute;blico emisor:</td>
                        <td class="meta-value"><?php echo htmlspecialchars($_SESSION['nombre_completo'] ?? 'Usuario Autorizado', ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                    <tr>
                        <td class="meta-label">Total de registros:</td>
                        <td class="meta-value"><?php echo number_format((int)($totalMovimientos ?? 0)); ?></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- ═══ Barra de Resumen / KPIs ═══ -->
    <table class="stats-table">
        <tr>
            <td>
                <span class="stat-label">Total Movimientos</span>
                <span class="stat-val"><?php echo number_format((int)($totalMovimientos ?? 0)); ?></span>
            </td>
            <td>
                <span class="stat-label">Unidades en Ruta</span>
                <span class="stat-val active"><?php echo number_format((int)($totalAbiertos ?? 0)); ?></span>
            </td>
            <td>
                <span class="stat-label">Retornos Concluidos</span>
                <span class="stat-val closed"><?php echo number_format((int)($totalCerrados ?? 0)); ?></span>
            </td>
            <td>
                <span class="stat-label">Km Totales Recorridos</span>
                <span class="stat-val"><?php echo number_format((int)($totalKmRecorridos ?? 0)); ?> km</span>
            </td>
        </tr>
    </table>

    <!-- ═══ Tabla de Movimientos ═══ -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">Folio</th>
                <th style="width: 14%;">Veh&iacute;culo</th>
                <th style="width: 7%;" class="text-center">Placas</th>
                <th style="width: 13%;">Conductor Oficial</th>
                <th style="width: 26%;">Destino, Motivo e Itinerario</th>
                <th style="width: 11%;" class="text-center">Salida / Km Ini</th>
                <th style="width: 11%;" class="text-center">Entrada / Km Fin</th>
                <th style="width: 6%;" class="text-center">Recorrido</th>
                <th style="width: 7%;" class="text-center">Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($movimientos)): ?>
                <?php foreach ($movimientos as $m): ?>
                    <tr>
                        <td class="text-center"><strong>#<?php echo (int)($m['id_movimiento'] ?? 0); ?></strong></td>
                        <td>
                            <strong><?php echo htmlspecialchars($m['vehiculo_marca'] ?? '', ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars($m['vehiculo_modelo'] ?? '', ENT_QUOTES, 'UTF-8'); ?></strong>
                            <br><span class="text-eco">Eco: #<?php echo htmlspecialchars($m['vehiculo_economico'] ?? 'S/N', ENT_QUOTES, 'UTF-8'); ?></span>
                        </td>
                        <td class="text-center">
                            <span class="badge-placas"><?php echo htmlspecialchars($m['vehiculo_placas'] ?? 'S/P', ENT_QUOTES, 'UTF-8'); ?></span>
                        </td>
                        <td><?php echo htmlspecialchars($m['conductor_nombre'] ?? 'Sin asignar', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($m['destino'] ?? '', ENT_QUOTES, 'UTF-8'); ?></strong>
                            <br><span class="text-eco"><?php echo htmlspecialchars($m['motivo_descripcion'] ?? 'General', ENT_QUOTES, 'UTF-8'); ?><?php echo !empty($m['especificacion_motivo']) ? ' &mdash; ' . htmlspecialchars($m['especificacion_motivo'], ENT_QUOTES, 'UTF-8') : ''; ?></span>
                            <?php
                            $itinerarioRaw = $m['itinerario_paradas'] ?? '';
                            $paradas = !empty($itinerarioRaw) ? json_decode($itinerarioRaw, true) : null;
                            if (is_array($paradas) && !empty($paradas)):
                            ?>
                                <div class="itinerario-box">
                                    <span class="itinerario-title">Escalas autorizadas:</span>
                                    <?php 
                                    $paradasTxt = [];
                                    foreach ($paradas as $idx => $p) {
                                        $num = $idx + 1;
                                        $ubi = trim($p['ubicacion'] ?? '');
                                        $mot = trim($p['motivo'] ?? '');
                                        if ($ubi !== '') {
                                            $paradasTxt[] = $mot !== '' ? "{$num}. {$ubi} ({$mot})" : "{$num}. {$ubi}";
                                        }
                                    }
                                    echo htmlspecialchars(implode(' | ', $paradasTxt), ENT_QUOTES, 'UTF-8');
                                    ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php 
                            $fSalida = !empty($m['fecha_hora_salida']) ? date('d/m/Y H:i', strtotime($m['fecha_hora_salida'])) : '';
                            echo htmlspecialchars($fSalida, ENT_QUOTES, 'UTF-8'); 
                            ?>
                            <br><span class="text-eco"><?php echo number_format((float)($m['km_inicial'] ?? 0)); ?> km</span>
                        </td>
                        <td class="text-center">
                            <?php if (!empty($m['fecha_hora_entrada'])): ?>
                                <?php echo date('d/m/Y H:i', strtotime($m['fecha_hora_entrada'])); ?>
                                <br><span class="text-eco"><?php echo number_format((float)($m['km_final'] ?? 0)); ?> km</span>
                            <?php else: ?>
                                <span class="badge-status badge-status-en-ruta">En ruta</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php echo $m['km_recorridos'] !== null ? number_format((float)$m['km_recorridos']) . ' km' : '<span class="empty-cell">&mdash;</span>'; ?>
                        </td>
                        <td class="text-center">
                            <?php 
                                $estMov = $m['estado_movimiento'] ?? 'Abierto';
                                $badgeClass = $estMov === 'Cerrado' ? 'badge-status-cerrado' : 'badge-status-en-ruta';
                            ?>
                            <span class="badge-status <?php echo $badgeClass; ?>">
                                <?php echo htmlspecialchars($estMov, ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="text-center" style="padding: 12px; color: #64748b; font-style: italic;">
                        No se encontraron registros de movimientos vehiculares registrados en la bit&aacute;cora.
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
                    Sistema Integral de Control Vehicular &bull; Secretar&iacute;a de Contralor&iacute;a del Estado de Durango (SECOTED) &bull; Documento Oficial de Consulta y Auditor&iacute;a
                </td>
                <td style="text-align: right; width: 30%;">
                    Fecha de emisi&oacute;n: <?php echo date('d/m/Y H:i:s'); ?>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
