<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../app/middleware/AuthMiddleware.php';
require_once __DIR__ . '/../app/middleware/RoleMiddleware.php';
require_once __DIR__ . '/../app/models/SolicitudModel.php';
require_once __DIR__ . '/../app/models/MovimientoModel.php';

$paginaActiva   = $paginaActual ?? 'dashboard';
$nombreUsuario  = $_SESSION['nombre_completo'] ?? $_SESSION['correo'] ?? 'Usuario';
$nombreRol      = RoleMiddleware::getNombreRol();

// Consulta inicial ligera de contadores para renderizado directo desde el backend
$badgePendientes = 0;
$badgeDespachos  = 0;

try {
    $dbSidebar  = Database::getConnection();
    $idRolUser  = (int)AuthMiddleware::idRol();
    $idAreaUser = (int)AuthMiddleware::idArea();

    if (RoleMiddleware::tieneRol([RoleMiddleware::ROL_ADMINISTRADOR, RoleMiddleware::ROL_JEFE_AREA])) {
        $solModelSidebar = new SolicitudModel($dbSidebar);
        $filtroArea = ($idRolUser === RoleMiddleware::ROL_JEFE_AREA) ? $idAreaUser : null;
        $badgePendientes = $solModelSidebar->contarPendientes($filtroArea);
    }

    if (RoleMiddleware::tieneRol([RoleMiddleware::ROL_ADMINISTRADOR, RoleMiddleware::ROL_ENCARGADO_VEHICULAR])) {
        $movModelSidebar = new MovimientoModel($dbSidebar);
        $badgeDespachos  = $movModelSidebar->contarSolicitudesListasSalida();
    }
} catch (Throwable $e) {
    error_log('Error en consulta de badges en sidebar.php: ' . $e->getMessage());
}
?>
<aside class="app-sidebar">
    <div class="sidebar-header">
        <img src="<?php echo isset($pathToAssets) ? $pathToAssets : 'assets/'; ?>img/Logo_D.webp" alt="Logo Durango" class="sidebar-logo">
        <div class="sidebar-brand">
            Control Vehicular
            <small>SECOTED</small>
        </div>
    </div>

    <nav class="sidebar-nav">
        <?php if (!RoleMiddleware::esEncargadoVehicular()): ?>
        <div class="nav-section-title">Principal</div>
        
        <a href="index.php?action=dashboard" class="nav-link <?php echo $paginaActiva === 'dashboard' ? 'active' : ''; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            <span>Inicio</span>
        </a>
        <?php endif; ?>

        <?php if (!RoleMiddleware::esSolicitante()): ?>
        <div class="nav-section-title">Operación</div>

        <?php if (RoleMiddleware::tieneRol([RoleMiddleware::ROL_ADMINISTRADOR, RoleMiddleware::ROL_ENCARGADO_VEHICULAR])): ?>
        <a href="index.php?action=caseta" class="nav-link <?php echo in_array($paginaActiva, ['caseta', 'movimientos'], true) ? 'active' : ''; ?>" id="navLinkCaseta">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
            <span>Control de Caseta</span>
            <span id="dotNavCaseta" class="sidebar-badge-count<?php echo $badgeDespachos > 0 ? '' : ' hidden'; ?>"><?php echo $badgeDespachos > 0 ? ($badgeDespachos > 99 ? '99+' : $badgeDespachos) : ''; ?></span>
        </a>
        <?php endif; ?>

        <?php if (RoleMiddleware::tieneRol([RoleMiddleware::ROL_ADMINISTRADOR, RoleMiddleware::ROL_JEFE_AREA])): ?>
        <a href="index.php?action=solicitudes_evaluar" class="nav-link <?php echo $paginaActiva === 'solicitudes_evaluar' ? 'active' : ''; ?>" id="navLinkEvaluar">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
            <span>Evaluar Solicitudes</span>
            <span id="dotNavEvaluar" class="sidebar-badge-count<?php echo $badgePendientes > 0 ? '' : ' hidden'; ?>"><?php echo $badgePendientes > 0 ? ($badgePendientes > 99 ? '99+' : $badgePendientes) : ''; ?></span>
        </a>
        <?php endif; ?>

        <?php if (RoleMiddleware::tieneRol([RoleMiddleware::ROL_ADMINISTRADOR, RoleMiddleware::ROL_JEFE_AREA])): ?>
        <a href="index.php?action=vehiculos" class="nav-link <?php echo $paginaActiva === 'vehiculos' ? 'active' : ''; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
            <span>Vehículos</span>
        </a>
        <?php endif; ?>

        <?php if (RoleMiddleware::esAdmin()): ?>
        <a href="index.php?action=conductores" class="nav-link <?php echo $paginaActiva === 'conductores' ? 'active' : ''; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <span>Conductores</span>
        </a>
        <?php endif; ?>
        <?php endif; ?>

        <?php if (RoleMiddleware::esAdmin()): ?>
        <div class="nav-section-title">Administración</div>

        <a href="index.php?action=areas" class="nav-link <?php echo $paginaActiva === 'areas' ? 'active' : ''; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            <span>Áreas</span>
        </a>

        <a href="index.php?action=reportes" class="nav-link <?php echo $paginaActiva === 'reportes' ? 'active' : ''; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            <span>Reportes y Bitácora</span>
        </a>
        <?php endif; ?>
        <!--Sección de control de Usuarios-->
        <?php if (RoleMiddleware::esAdmin()): ?>
            <a href="index.php?action=usuarios" class="nav-link <?php echo $paginaActiva === 'usuarios' ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <span>Usuarios</span>
            </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-user">
        <div class="user-info">
            <span class="user-name" title="<?php echo htmlspecialchars($nombreUsuario, ENT_QUOTES, 'UTF-8'); ?>">
                <?php echo htmlspecialchars($nombreUsuario, ENT_QUOTES, 'UTF-8'); ?>
            </span>
            <span class="user-badge"><?php echo htmlspecialchars($nombreRol, ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
        <a href="logout.php" class="btn-logout" title="Cerrar Sesión">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        </a>
    </div>
</aside>
