<?php

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/RoleMiddleware.php';
require_once __DIR__ . '/../models/UsuarioModel.php';
require_once __DIR__ . '/../models/ConductorModel.php';
require_once __DIR__ . '/../models/AreaModel.php';
require_once __DIR__ . '/../models/BitacoraModel.php';

/**
 * ConductorController.php
 * Controlador para la gestión y padrón de conductores oficiales basado en la tabla usuarios.
 * Consolida el padrón vehicular con los usuarios institucionales que poseen licencia de conducir.
 */
class ConductorController
{
    public function index(): void
    {
        AuthMiddleware::verificarSesion();
        RoleMiddleware::requerirRol(RoleMiddleware::ROL_ADMINISTRADOR);

        $busqueda  = trim($_GET['busqueda'] ?? $_GET['q'] ?? '');
        $pagina    = max(1, (int)($_GET['pagina'] ?? $_GET['page'] ?? 1));
        $porPagina = 10;
        $offset    = ($pagina - 1) * $porPagina;

        $conductores         = [];
        $usuariosDisponibles = [];
        $areas               = [];
        $totalRegistros      = 0;
        $totalPaginas        = 1;

        try {
            $db             = Database::getConnection();
            $conductorModel = new ConductorModel($db);
            $usuarioModel   = new UsuarioModel($db);
            $areaModel      = new AreaModel($db);

            $totalRegistros = $conductorModel->contarTotal($busqueda);
            $totalPaginas   = (int)ceil($totalRegistros / $porPagina);
            if ($totalPaginas < 1) {
                $totalPaginas = 1;
            }

            if ($pagina > $totalPaginas && $totalRegistros > 0) {
                $pagina = $totalPaginas;
                $offset = ($pagina - 1) * $porPagina;
            }

            $conductores         = $conductorModel->obtenerPaginados($porPagina, $offset, $busqueda);
            $usuariosDisponibles = $usuarioModel->obtenerTodos();
            $areas               = $areaModel->obtenerTodas();
        } catch (Throwable $e) {
            error_log('Error en ConductorController::index(): ' . $e->getMessage());
        }

        $tituloPagina = 'Directorio de Conductores';
        $paginaActual = 'conductores';

        require_once __DIR__ . '/../views/conductores/index.php';
    }


    public function store(): void
    {
        AuthMiddleware::verificarSesion();
        RoleMiddleware::requerirRol(RoleMiddleware::ROL_ADMINISTRADOR);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idUsuario      = (int)($_POST['id_usuario'] ?? 0);
            $numeroLicencia = trim($_POST['numero_licencia'] ?? '');
            $vigencia       = !empty($_POST['vigencia_licencia']) ? $_POST['vigencia_licencia'] : null;
            $fotoLicencia   = $this->procesarFotoLicencia();

            if ($idUsuario <= 0 || empty($numeroLicencia)) {
                $_SESSION['alerta'] = [
                    'tipo' => 'danger',
                    'mensaje' => 'Debe seleccionar un usuario y registrar el número de licencia.'
                ];
                header('Location: index.php?action=conductores');
                exit();
            }

            try {
                $db = Database::getConnection();
                $usuarioModel = new UsuarioModel($db);

                $usuario = $usuarioModel->obtenerPorId($idUsuario);
                if (!$usuario) {
                    throw new Exception('El usuario seleccionado no existe o no se encuentra activo.');
                }

                $exito = $usuarioModel->actualizarLicencia($idUsuario, $numeroLicencia, $vigencia, $fotoLicencia);

                if ($exito) {
                    try {
                        $bitacora = new BitacoraModel($db);
                        $bitacora->registrar(
                            (int)AuthMiddleware::idUsuario(),
                            'usuarios',
                            $idUsuario,
                            'ACTUALIZAR',
                            "Alta en padrón de conductores: {$usuario['nombre_completo']} (Licencia: {$numeroLicencia})"
                        );
                    } catch (Throwable $e) {
                        error_log('Error bitácora ConductorController::store: ' . $e->getMessage());
                    }

                    $_SESSION['alerta'] = [
                        'tipo' => 'success',
                        'mensaje' => 'Conductor incorporado al padrón oficial exitosamente.'
                    ];
                    header('Location: index.php?action=conductores');
                    exit();
                }

                throw new Exception('No fue posible registrar los datos de la licencia.');
            } catch (Exception $e) {
                $_SESSION['alerta'] = [
                    'tipo' => 'danger',
                    'mensaje' => $e->getMessage()
                ];
                header('Location: index.php?action=conductores');
                exit();
            }
        }

        header('Location: index.php?action=conductores');
        exit();
    }

    public function editar(): void
    {
        AuthMiddleware::verificarSesion();
        RoleMiddleware::requerirRol(RoleMiddleware::ROL_ADMINISTRADOR);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idUsuario      = (int)($_POST['id_usuario'] ?? 0);
            $numeroLicencia = trim($_POST['numero_licencia'] ?? '');
            $vigencia       = !empty($_POST['vigencia_licencia']) ? $_POST['vigencia_licencia'] : null;
            $fotoLicencia   = $this->procesarFotoLicencia();

            if ($idUsuario <= 0 || empty($numeroLicencia)) {
                $_SESSION['alerta'] = [
                    'tipo' => 'danger',
                    'mensaje' => 'El número de licencia es requerido para mantener al conductor en el padrón.'
                ];
                header('Location: index.php?action=conductores');
                exit();
            }

            try {
                $db = Database::getConnection();
                $usuarioModel = new UsuarioModel($db);

                $usuario = $usuarioModel->obtenerPorId($idUsuario);
                if (!$usuario) {
                    throw new Exception('Usuario no encontrado.');
                }

                $exito = $usuarioModel->actualizarLicencia($idUsuario, $numeroLicencia, $vigencia, $fotoLicencia);

                if ($exito) {
                    try {
                        $bitacora = new BitacoraModel($db);
                        $bitacora->registrar(
                            (int)AuthMiddleware::idUsuario(),
                            'usuarios',
                            $idUsuario,
                            'ACTUALIZAR',
                            "Actualización de licencia para conductor: {$usuario['nombre_completo']} (No: {$numeroLicencia})"
                        );
                    } catch (Throwable $e) {
                        error_log('Error bitácora ConductorController::editar: ' . $e->getMessage());
                    }

                    $_SESSION['alerta'] = [
                        'tipo' => 'success',
                        'mensaje' => 'Datos de licencia actualizados correctamente.'
                    ];
                    header('Location: index.php?action=conductores');
                    exit();
                }

                throw new Exception('No fue posible actualizar la licencia.');
            } catch (Exception $e) {
                $_SESSION['alerta'] = [
                    'tipo' => 'danger',
                    'mensaje' => $e->getMessage()
                ];
                header('Location: index.php?action=conductores');
                exit();
            }
        }

        header('Location: index.php?action=conductores');
        exit();
    }

    public function eliminar(): void
    {
        AuthMiddleware::verificarSesion();
        RoleMiddleware::requerirRol(RoleMiddleware::ROL_ADMINISTRADOR);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idUsuario = (int)($_POST['id_usuario'] ?? 0);

            if ($idUsuario <= 0) {
                $_SESSION['alerta'] = [
                    'tipo' => 'danger',
                    'mensaje' => 'Identificador de conductor inválido.'
                ];
                header('Location: index.php?action=conductores');
                exit();
            }

            try {
                $db = Database::getConnection();
                $usuarioModel = new UsuarioModel($db);
                $usuario = $usuarioModel->obtenerPorId($idUsuario);

                // Quitar datos de licencia para dar de baja del padrón de conductores
                $exito = $usuarioModel->actualizarLicencia($idUsuario, null, null, null);

                if ($exito) {
                    try {
                        $bitacora = new BitacoraModel($db);
                        $bitacora->registrar(
                            (int)AuthMiddleware::idUsuario(),
                            'usuarios',
                            $idUsuario,
                            'ACTUALIZAR',
                            "Baja del padrón de conductores para usuario: " . ($usuario['nombre_completo'] ?? "#{$idUsuario}")
                        );
                    } catch (Throwable $e) {
                        error_log('Error bitácora ConductorController::eliminar: ' . $e->getMessage());
                    }

                    $_SESSION['alerta'] = [
                        'tipo' => 'success',
                        'mensaje' => 'El servidor público ha sido retirado del padrón de conductores.'
                    ];
                    header('Location: index.php?action=conductores');
                    exit();
                }

                throw new Exception('No fue posible retirar la licencia del usuario.');
            } catch (Exception $e) {
                $_SESSION['alerta'] = [
                    'tipo' => 'danger',
                    'mensaje' => $e->getMessage()
                ];
                header('Location: index.php?action=conductores');
                exit();
            }
        }

        header('Location: index.php?action=conductores');
        exit();
    }

    /**
     * Procesa la carga opcional de imagen de licencia o lectura de enlace/archivo.
     */
    private function procesarFotoLicencia(): ?string
    {
        if (!empty($_FILES['foto_licencia']['name']) && $_FILES['foto_licencia']['error'] === UPLOAD_ERR_OK) {
            $archivoTmp = $_FILES['foto_licencia']['tmp_name'];
            $tamano     = $_FILES['foto_licencia']['size'];
            $extension  = strtolower(pathinfo($_FILES['foto_licencia']['name'], PATHINFO_EXTENSION));

            $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
            if (!in_array($extension, $extensionesPermitidas, true)) {
                return null;
            }

            // Máximo 5MB
            if ($tamano > 5 * 1024 * 1024) {
                return null;
            }

            $carpetaDestino = __DIR__ . '/../../storage/uploads/licencias';
            if (!is_dir($carpetaDestino)) {
                mkdir($carpetaDestino, 0755, true);
            }

            $nombreArchivo = 'lic_' . uniqid('', true) . '.' . $extension;
            $rutaFinal = $carpetaDestino . '/' . $nombreArchivo;

            if (move_uploaded_file($archivoTmp, $rutaFinal)) {
                return 'storage:licencias/' . $nombreArchivo;
            }
        }

        // Si se envió como texto / ruta existente
        $fotoTexto = trim($_POST['foto_licencia_actual'] ?? '');
        return !empty($fotoTexto) ? $fotoTexto : null;
    }
}
