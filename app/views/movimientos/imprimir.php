<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($tituloDocumento ?? 'Bitácora de Movimientos', ENT_QUOTES, 'UTF-8'); ?> — SECOTED</title>
    <style>
        /* ═══════════════════════════════════════════════════════════════
           CONFIGURACIÓN DE PÁGINA Y TIPOGRAFÍA BASE (DOMPDF NATIVO)
           ═══════════════════════════════════════════════════════════════ */
        @page {
            size: letter landscape;
            margin: 12mm 15mm 15mm 15mm;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Helvetica, Arial, sans-serif;
            background-color: #ffffff;
            color: #0f172a;
            font-size: 7.5pt;
            line-height: 1.3;
        }

        /* ═══════════════════════════════════════════════════════════════
           ENCABEZADO INSTITUCIONAL COMPACTO (ALTURA ≤ 2 CM)
           ═══════════════════════════════════════════════════════════════ */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #0f172a;
            margin-bottom: 6px;
            padding-bottom: 4px;
        }

        .header-left {
            text-align: left;
            vertical-align: top;
            width: 62%;
            padding-bottom: 4px;
        }

        .header-gov {
            font-size: 9.5pt;
            font-weight: bold;
            color: #0f172a;
            letter-spacing: 0.2px;
            text-transform: uppercase;
        }

        .header-secretaria {
            font-size: 8pt;
            font-weight: bold;
            color: #334155;
            margin-top: 1px;
            text-transform: uppercase;
        }

        .header-modulo {
            font-size: 7.5pt;
            color: #64748b;
            margin-top: 2px;
        }

        .header-right {
            text-align: right;
            vertical-align: top;
            width: 38%;
            padding-bottom: 4px;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7pt;
        }

        .meta-table td {
            padding: 1px 2px;
            vertical-align: top;
        }

        .meta-label {
            color: #64748b;
            text-align: right;
            font-weight: normal;
            width: 48%;
        }

        .meta-value {
            color: #0f172a;
            text-align: left;
            font-weight: bold;
            padding-left: 5px;
            width: 52%;
        }

        /* ═══════════════════════════════════════════════════════════════
           BARRA DE RESUMEN COMPACTA (KPIS)
           ═══════════════════════════════════════════════════════════════ */
        .stats-table {
            width: 100%;
            border-collapse: collapse;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            margin-bottom: 6px;
        }

        .stats-table td {
            padding: 4px 6px;
            font-size: 7pt;
            text-align: center;
            border-right: 1px solid #cbd5e1;
        }

        .stats-table td:last-child {
            border-right: none;
        }

        .stat-label {
            color: #64748b;
            text-transform: uppercase;
            font-size: 6pt;
            font-weight: bold;
            display: block;
            margin-bottom: 1px;
        }

        .stat-val {
            font-size: 8.5pt;
            font-weight: bold;
            color: #0f172a;
        }

        .stat-val.active {
            color: #b45309;
        }

        .stat-val.closed {
            color: #15803d;
        }

        /* ═══════════════════════════════════════════════════════════════
           TABLA DE DATOS DE LA BITÁCORA
           ═══════════════════════════════════════════════════════════════ */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 7pt;
        }

        .data-table thead tr {
            background-color: #0f172a;
            color: #ffffff;
        }

        .data-table th {
            padding: 4px 3px;
            font-size: 6.8pt;
            font-weight: bold;
            border: 1px solid #0f172a;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 0.2px;
            vertical-align: middle;
        }

        .data-table th.text-center,
        .data-table td.text-center {
            text-align: center;
        }

        .data-table tbody td {
            padding: 3px 4px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
            line-height: 1.25;
            word-wrap: break-word;
        }

        .data-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .data-table tbody tr {
            page-break-inside: avoid;
        }

        /* ─── Clases de Utilidad y Badges ────────────────────────── */
        .text-eco {
            color: #64748b;
            font-size: 6.5pt;
        }

        .badge-placas {
            font-family: monospace;
            font-size: 7pt;
            font-weight: bold;
            color: #0f172a;
        }

        .badge-status {
            display: inline-block;
            padding: 1px 4px;
            font-size: 6.2pt;
            font-weight: bold;
            border-radius: 2px;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }

        .badge-status-en-ruta {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            color: #92400e;
        }

        .badge-status-cerrado {
            background-color: #dcfce7;
            border: 1px solid #22c55e;
            color: #166534;
        }

        .itinerario-box {
            margin-top: 3px;
            padding: 2px 4px;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 2px;
            font-size: 6.2pt;
            color: #334155;
            line-height: 1.2;
        }

        .itinerario-title {
            font-weight: bold;
            color: #0369a1;
            display: block;
            margin-bottom: 1px;
            text-transform: uppercase;
            font-size: 5.8pt;
        }

        .empty-cell {
            color: #94a3b8;
            font-style: italic;
        }

        /* ═══════════════════════════════════════════════════════════════
           PIE DE PÁGINA INSTITUCIONAL
           ═══════════════════════════════════════════════════════════════ */
        .footer {
            position: fixed;
            bottom: -11mm;
            left: 0;
            right: 0;
            height: 6mm;
            border-top: 1px solid #cbd5e1;
            padding-top: 2px;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 6.2pt;
            color: #64748b;
        }
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
