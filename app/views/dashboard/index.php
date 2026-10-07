<?php
require_once __DIR__ . '/../../../includes/header.php';
require_once __DIR__ . '/../../../includes/sidebar.php';
?>

<main class="app-main dashboard-main">
    <!-- A. Cabecera Ejecutiva -->
    <header class="app-topbar dashboard-topbar">
        <div>
            <h1 class="dashboard-title">
                Monitoreo General de Control Vehicular
            </h1>
            <p class="dashboard-subtitle">
                SECOTED Durango — Panel Operativo
            </p>
        </div>
        <div class="topbar-right dashboard-topbar-right">
            <span class="topbar-date"><?php echo date('d/m/Y H:i:s'); ?></span>
        </div>
    </header>

    <div class="app-content dashboard-content">
        <?php require_once __DIR__ . '/../layouts/alertas.php'; ?>
        <!-- B. Tarjetas KPI Métricas Reales (4 Columnas) -->
        <div class="kpi-grid">
            <!-- KPI 1: Parque Vehicular Total -->
            <div class="kpi-card">
                <div class="kpi-header">
                    <span class="kpi-title">Parque Vehicular Total</span>
                    <span class="kpi-icon-wrap kpi-icon-default">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                    </span>
                </div>
                <div class="kpi-value font-monospace">
                    <?php echo (int)($kpis['total_vehiculos'] ?? 0); ?>
                </div>
                <div class="kpi-footer">
                    <span class="kpi-chip kpi-chip--success">
                        <?php echo (int)($kpis['vehiculos_disponibles'] ?? 0); ?> disp.
                    </span>
                    <span class="kpi-footer-text">
                        · <?php echo (int)($kpis['vehiculos_en_ruta'] ?? 0); ?> en ruta · <?php echo (int)($kpis['vehiculos_mantenimiento'] ?? 0); ?> taller
                    </span>
                </div>
            </div>

            <!-- KPI 2: Solicitudes Pendientes -->
            <a href="index.php?action=solicitudes_evaluar" class="kpi-card text-decoration-none">
                <div class="kpi-header">
                    <span class="kpi-title">Solicitudes Pendientes</span>
                    <span class="kpi-icon-wrap kpi-icon-warning">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    </span>
                </div>
                <div class="kpi-value font-monospace <?php echo ($kpis['solicitudes_pendientes'] ?? 0) > 0 ? 'kpi-value--warning' : ''; ?>">
                    <?php echo (int)($kpis['solicitudes_pendientes'] ?? 0); ?>
                </div>
                <div class="kpi-footer">
                    <?php if (($kpis['solicitudes_pendientes'] ?? 0) > 0): ?>
                        <span class="kpi-chip kpi-chip--warning">
                            Requiere Evaluación &rarr;
                        </span>
                        <span class="kpi-footer-text">En espera de firma</span>
                    <?php else: ?>
                        <span class="kpi-chip kpi-chip--neutral">
                            Al día
                        </span>
                        <span class="kpi-footer-text">Sin trámites en cola</span>
                    <?php endif; ?>
                </div>
            </a>

            <!-- KPI 3: Salidas en Caseta Hoy -->
            <div class="kpi-card">
                <div class="kpi-header">
                    <span class="kpi-title">Salidas en Caseta Hoy</span>
                    <span class="kpi-icon-wrap kpi-icon-success">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                    </span>
                </div>
                <div class="kpi-value font-monospace">
                    <?php echo (int)($kpis['movimientos_hoy'] ?? 0); ?>
                </div>
                <div class="kpi-footer">
                    <?php if (($kpis['retornos_pendientes'] ?? 0) > 0): ?>
                        <span class="kpi-chip kpi-chip--info">
                            <?php echo (int)($kpis['retornos_pendientes'] ?? 0); ?> activos
                        </span>
                        <span class="kpi-footer-text">Pendientes de retorno</span>
                    <?php else: ?>
                        <span class="kpi-chip kpi-chip--neutral">
                            0 en tránsito
                        </span>
                        <span class="kpi-footer-text">Caseta despejada</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- KPI 4: Choferes Activos -->
            <div class="kpi-card">
                <div class="kpi-header">
                    <span class="kpi-title">Choferes Activos</span>
                    <span class="kpi-icon-wrap kpi-icon-accent">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </span>
                </div>
                <div class="kpi-value font-monospace">
                    <?php echo (int)($kpis['total_conductores'] ?? 0); ?>
                </div>
                <div class="kpi-footer">
                    <span class="kpi-chip kpi-chip--success">
                        Personal Oficial
                    </span>
                    <span class="kpi-footer-text">Padrón acreditado</span>
                </div>
            </div>
        </div>

        <!-- C. Bloque de Gráficas Ejecutivas (Row de 2 Columnas) -->
        <div class="charts-row">
            <!-- Gráfica 1: Donut Chart -->
            <div class="chart-card">
                <div class="chart-card-header">
                    <div>
                        <h3 class="chart-card-title">Estado Operativo del Parque Vehicular</h3>
                        <span class="chart-card-subtitle">Distribución porcentual de flota vehicular</span>
                    </div>
                    <span class="badge chart-badge-neutral">
                        <?php echo (int)($kpis['total_vehiculos'] ?? 0); ?> Unidades
                    </span>
                </div>
                <div class="chart-container-wrap">
                    <canvas id="chartEstadoVehiculos"></canvas>
                </div>
                <div class="chart-legend-custom">
                    <div class="legend-item">
                        <span class="legend-bullet legend-bullet-success"></span>
                        <span class="legend-label">Disponibles</span>
                        <strong class="legend-val font-monospace"><?php echo (int)($chartVehiculos['Disponibles'] ?? 0); ?></strong>
                    </div>
                    <div class="legend-item">
                        <span class="legend-bullet legend-bullet-warning"></span>
                        <span class="legend-label">En Ruta</span>
                        <strong class="legend-val font-monospace"><?php echo (int)($chartVehiculos['En ruta'] ?? 0); ?></strong>
                    </div>
                    <div class="legend-item">
                        <span class="legend-bullet legend-bullet-neutral"></span>
                        <span class="legend-label">En Mantenimiento</span>
                        <strong class="legend-val font-monospace"><?php echo (int)($chartVehiculos['En mantenimiento'] ?? 0); ?></strong>
                    </div>
                </div>
            </div>

            <!-- Gráfica 2: Bar Chart -->
            <div class="chart-card">
                <div class="chart-card-header">
                    <div>
                        <h3 class="chart-card-title">Balance de Solicitudes de Comisión</h3>
                        <span class="chart-card-subtitle">Estatus de autorizaciones institucionales</span>
                    </div>
                    <span class="badge chart-badge-neutral">
                        Histórico Global
                    </span>
                </div>
                <div class="chart-container-wrap">
                    <canvas id="chartSolicitudes"></canvas>
                </div>
                <div class="chart-legend-custom">
                    <div class="legend-item">
                        <span class="legend-bullet legend-bullet-primary"></span>
                        <span class="legend-label">Autorizadas</span>
                        <strong class="legend-val font-monospace"><?php echo (int)($chartSolicitudes['Autorizadas'] ?? 0); ?></strong>
                    </div>
                    <div class="legend-item">
                        <span class="legend-bullet legend-bullet-warning"></span>
                        <span class="legend-label">Pendientes</span>
                        <strong class="legend-val font-monospace"><?php echo (int)($chartSolicitudes['Pendientes'] ?? 0); ?></strong>
                    </div>
                    <div class="legend-item">
                        <span class="legend-bullet legend-bullet-danger"></span>
                        <span class="legend-label">Rechazadas</span>
                        <strong class="legend-val font-monospace"><?php echo (int)($chartSolicitudes['Rechazadas'] ?? 0); ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- D. Monitor de Caseta en Tiempo Real (Tabla de Alta Densidad) -->
        <div class="content-card dashboard-table-card">
            <div class="card-header dashboard-table-header">
                <div>
                    <h3 class="dashboard-table-title">
                        Monitor de Caseta en Tiempo Real
                    </h3>
                    <span class="dashboard-table-desc">Unidades actualmente en ruta y registros de la jornada</span>
                </div>
                <a href="index.php?action=caseta" class="btn btn-dark btn-sm dashboard-btn-link">
                    Ver Bitácora Completa &rarr;
                </a>
            </div>
            <div class="card-body dashboard-table-body">
                <div class="table-responsive">
                    <table class="table table-hover modern-table dashboard-table">
                        <thead>
                            <tr class="dashboard-tr-head">
                                <th class="dashboard-th">Vehículo / Placas</th>
                                <th class="dashboard-th">Conductor Asignado</th>
                                <th class="dashboard-th">Destino / Motivo</th>
                                <th class="dashboard-th">Hora de Salida</th>
                                <th class="dashboard-th dashboard-th-center">Estatus</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($movimientosActivos)): ?>
                                <?php foreach ($movimientosActivos as $m): ?>
                                    <tr>
                                        <td class="dashboard-td">
                                            <div class="dashboard-cell-title">
                                                #<?php echo htmlspecialchars($m['numero_economico'] ?? 'S/N', ENT_QUOTES, 'UTF-8'); ?> — <?php echo htmlspecialchars(($m['marca'] ?? '') . ' ' . ($m['modelo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                            <span class="font-monospace dashboard-plate-badge">
                                                <?php echo htmlspecialchars($m['placas'] ?? 'N/D', ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </td>
                                        <td class="dashboard-td">
                                            <span class="dashboard-cell-secondary">
                                                <?php echo htmlspecialchars($m['conductor_nombre'] ?? 'Chofer no asignado', ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </td>
                                        <td class="dashboard-td">
                                            <div class="dashboard-cell-driver">
                                                <?php echo htmlspecialchars($m['destino'] ?? 'No especificado', ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                            <small class="dashboard-cell-muted">
                                                <?php echo htmlspecialchars($m['motivo_descripcion'] ?? 'Oficial', ENT_QUOTES, 'UTF-8'); ?>
                                            </small>
                                        </td>
                                        <td class="dashboard-td">
                                            <span class="font-monospace dashboard-cell-mono">
                                                <?php echo htmlspecialchars(date('H:i', strtotime($m['fecha_hora_salida'] ?? 'now')), ENT_QUOTES, 'UTF-8'); ?> hrs
                                            </span>
                                            <small class="dashboard-cell-sub">
                                                <?php echo htmlspecialchars(date('d/m/Y', strtotime($m['fecha_hora_salida'] ?? 'now')), ENT_QUOTES, 'UTF-8'); ?>
                                            </small>
                                        </td>
                                        <td class="dashboard-td-center">
                                            <?php if (($m['estado_movimiento'] ?? '') === 'Abierto'): ?>
                                                <span class="kpi-chip kpi-chip--warning chip-status-transit">
                                                    <span class="status-dot-amber"></span>
                                                    En Ruta
                                                </span>
                                            <?php else: ?>
                                                <span class="kpi-chip kpi-chip--success">
                                                    Concluido
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="dashboard-empty-state">
                                        <svg class="dashboard-empty-icon" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#CBD5E1" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                                        <div class="dashboard-empty-title">No hay unidades en ruta en este momento</div>
                                        <div class="dashboard-empty-desc">El parque vehicular operativo se encuentra en resguardo.</div>
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

<!-- Chart.js CDN Oficial -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configuración tipográfica y de color sobria para Chart.js
    Chart.defaults.font.family = "'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif";
    Chart.defaults.color = '#64748B';

    // 1. Gráfica Donut: Estado Operativo del Parque Vehicular
    const ctxVeh = document.getElementById('chartEstadoVehiculos');
    if (ctxVeh) {
        const dataVehiculos = {
            labels: ['Disponibles', 'En Ruta', 'En Mantenimiento'],
            datasets: [{
                data: [
                    <?php echo (int)($chartVehiculos['Disponibles'] ?? 0); ?>,
                    <?php echo (int)($chartVehiculos['En ruta'] ?? 0); ?>,
                    <?php echo (int)($chartVehiculos['En mantenimiento'] ?? 0); ?>
                ],
                backgroundColor: [
                    '#10B981', // Verde esmeralda sobrio
                    '#F59E0B', // Ámbar institucional
                    '#64748B'  // Pizarra neutro
                ],
                borderWidth: 2,
                borderColor: '#FFFFFF',
                hoverOffset: 4
            }]
        };

        new Chart(ctxVeh, {
            type: 'doughnut',
            data: dataVehiculos,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: {
                        display: false // Usamos la botonera custom debajo
                    },
                    tooltip: {
                        backgroundColor: '#1E2230',
                        titleColor: '#FFFFFF',
                        bodyColor: '#E2E8F0',
                        padding: 10,
                        boxPadding: 4,
                        usePointStyle: true,
                        callbacks: {
                            label: function(context) {
                                const val = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const pct = total > 0 ? Math.round((val / total) * 100) : 0;
                                return ` ${context.label}: ${val} unidades (${pct}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    // 2. Gráfica de Barras: Balance de Solicitudes de Comisión
    const ctxSol = document.getElementById('chartSolicitudes');
    if (ctxSol) {
        const dataSolicitudes = {
            labels: ['Autorizadas', 'Pendientes', 'Rechazadas'],
            datasets: [{
                label: 'Solicitudes',
                data: [
                    <?php echo (int)($chartSolicitudes['Autorizadas'] ?? 0); ?>,
                    <?php echo (int)($chartSolicitudes['Pendientes'] ?? 0); ?>,
                    <?php echo (int)($chartSolicitudes['Rechazadas'] ?? 0); ?>
                ],
                backgroundColor: [
                    '#1E2230', // Azul marino institucional
                    '#F59E0B', // Ámbar evaluación
                    '#EF4444'  // Rojo suave desaturado
                ],
                borderRadius: 6,
                maxBarThickness: 42
            }]
        };

        new Chart(ctxSol, {
            type: 'bar',
            data: dataSolicitudes,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1E2230',
                        titleColor: '#FFFFFF',
                        bodyColor: '#E2E8F0',
                        padding: 10,
                        callbacks: {
                            label: function(context) {
                                return ` ${context.dataset.label}: ${context.raw} comisiones`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { weight: '600', size: 12 } }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            precision: 0
                        },
                        grid: {
                            color: '#F1F5F9',
                            drawBorder: false
                        }
                    }
                }
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
