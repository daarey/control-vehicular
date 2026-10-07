<?php

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/RoleMiddleware.php';
require_once __DIR__ . '/../models/UsuarioModel.php';
require_once __DIR__ . '/../models/AreaModel.php';
require_once __DIR__ . '/../models/BitacoraModel.php';

/**
 * UsuarioController.php
 * Controlador para la administración centralizada de usuarios institucionales (SECOTED).
 * Restringido exclusivamente al Administrador del Sistema.
 */
class UsuarioController
{
    /**
     * Muestra la lista de usuarios activos, roles y áreas.
     */
    public function index(): void
    {
        AuthMiddleware::verificarSesion();
        RoleMiddleware::requerirRol(RoleMiddleware::ROL_ADMINISTRADOR);

        $usuarios = [];
        $roles    = [];
        $areas    = [];

        try {
            $db           = Database::getConnection();
            $usuarioModel = new UsuarioModel($db);
            $areaModel    = new AreaModel($db);

            $usuarios = $usuarioModel->obtenerTodos();
            $roles    = $usuarioModel->obtenerRoles();
            $areas    = $areaModel->obtenerTodas();
        } catch (Throwable $e) {
            error_log('Error en UsuarioController::index(): ' . $e->getMessage());
        }

        // Manejo de mensajes vía GET para retrocompatibilidad
        $mensaje = $_GET['mensaje'] ?? null;
        if ($mensaje === 'exito') {
            $_SESSION['alerta'] = ['tipo' => 'success', 'mensaje' => 'Usuario registrado exitosamente en el sistema.'];
        } elseif ($mensaje === 'editado') {
            $_SESSION['alerta'] = ['tipo' => 'success', 'mensaje' => 'Datos del usuario actualizados correctamente.'];
        } elseif ($mensaje === 'eliminado') {
            $_SESSION['alerta'] = ['tipo' => 'success', 'mensaje' => 'El usuario ha sido dado de baja correctamente.'];
        } elseif ($mensaje === 'error') {
            if (!isset($_SESSION['alerta'])) {
                $detalle = $_GET['detalle'] ?? '';
                if ($detalle === 'correo_duplicado') {
                    $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'El correo electrónico ya pertenece a una cuenta registrada.'];
                } elseif ($detalle === 'autoeliminacion') {
                    $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'Acción denegada: no puede dar de baja su propia cuenta de usuario.'];
                } else {
                    $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'Ocurrió un error al procesar la operación. Intente nuevamente.'];
                }
            }
        }

        $tituloPagina = 'Gestión de Usuarios';
        $paginaActual = 'usuarios';

        require_once __DIR__ . '/../views/usuarios/index.php';
    }

    /**
     * Registra un nuevo usuario con contraseña encriptada (BCRYPT).
     */
    public function store(): void
    {
        AuthMiddleware::verificarSesion();
        RoleMiddleware::requerirRol(RoleMiddleware::ROL_ADMINISTRADOR);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombreCompleto = trim($_POST['nombre_completo'] ?? '');
            $correo         = trim($_POST['correo'] ?? '');
            $password       = trim($_POST['password'] ?? '');
            $idRol          = (int)($_POST['id_rol'] ?? 0);
            $idArea         = !empty($_POST['id_area']) ? (int)$_POST['id_area'] : null;

            if (empty($nombreCompleto) || empty($correo) || empty($password) || $idRol <= 0) {
                $_SESSION['alerta'] = [
                    'tipo' => 'danger',
                    'mensaje' => 'Por favor complete todos los campos obligatorios (Nombre, correo, contraseña y rol).'
                ];
                $destino = $_SERVER['HTTP_REFERER'] ?? 'index.php?action=usuarios';
                header('Location: ' . $destino);
                exit();
            }

            try {
                $db           = Database::getConnection();
                $usuarioModel = new UsuarioModel($db);

                // Validar que el correo no esté registrado en una cuenta activa
                $usuarioExistente = $usuarioModel->buscarPorCorreo($correo);
                if ($usuarioExistente) {
                    throw new Exception('El correo electrónico ya pertenece a una cuenta registrada.');
                }

                // Encriptación obligatoria con BCRYPT
                $passwordHash = password_hash($password, PASSWORD_BCRYPT);

                $nuevoId = $usuarioModel->registrar([
                    'nombre_completo' => $nombreCompleto,
                    'correo'          => $correo,
                    'password'        => $passwordHash,
                    'id_rol'          => $idRol,
                    'id_area'         => $idArea,
                ]);

                if ($nuevoId) {
                    try {
                        $bitacora = new BitacoraModel($db);
                        $bitacora->registrar(
                            (int)AuthMiddleware::idUsuario(),
                            'usuarios',
                            $nuevoId,
                            'CREAR',
                            "Alta de nuevo usuario: {$nombreCompleto} ({$correo}) con Rol ID: {$idRol}"
                        );
                    } catch (Throwable $e) {
                        error_log('Error en bitácora al registrar usuario: ' . $e->getMessage());
                    }

                    $_SESSION['alerta'] = [
                        'tipo' => 'success',
                        'mensaje' => 'Registro completado exitosamente.'
                    ];
                    header('Location: index.php?action=usuarios');
                    exit();
                }

                throw new Exception('No fue posible completar el registro del usuario.');
            } catch (Exception $e) {
                $_SESSION['alerta'] = [
                    'tipo' => 'danger',
                    'mensaje' => $e->getMessage()
                ];
                $destino = $_SERVER['HTTP_REFERER'] ?? 'index.php?action=usuarios';
                header('Location: ' . $destino);
                exit();
            }
        }

        header('Location: index.php?action=usuarios');
        exit();
    }

    /**
     * Modifica los datos de un usuario existente.
     */
    public function update(): void
    {
        AuthMiddleware::verificarSesion();
        RoleMiddleware::requerirRol(RoleMiddleware::ROL_ADMINISTRADOR);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idUsuario      = (int)($_POST['id_usuario'] ?? 0);
            $nombreCompleto = trim($_POST['nombre_completo'] ?? '');
            $correo         = trim($_POST['correo'] ?? '');
            $password       = trim($_POST['password'] ?? '');
            $idRol          = (int)($_POST['id_rol'] ?? 0);
            $idArea         = !empty($_POST['id_area']) ? (int)$_POST['id_area'] : null;

            if ($idUsuario <= 0 || empty($nombreCompleto) || empty($correo) || $idRol <= 0) {
                $_SESSION['alerta'] = [
                    'tipo' => 'danger',
                    'mensaje' => 'Por favor complete todos los campos obligatorios para actualizar el usuario.'
                ];
                $destino = $_SERVER['HTTP_REFERER'] ?? 'index.php?action=usuarios';
                header('Location: ' . $destino);
                exit();
            }

            try {
                $db           = Database::getConnection();
                $usuarioModel = new UsuarioModel($db);

                // Validar si el correo cambió y pertenece a otro usuario
                $usuarioExistente = $usuarioModel->buscarPorCorreo($correo);
                if ($usuarioExistente && (int)$usuarioExistente['id_usuario'] !== $idUsuario) {
                    throw new Exception('El correo electrónico ya pertenece a una cuenta registrada.');
                }

                $actualizado = $usuarioModel->actualizar($idUsuario, [
                    'nombre_completo' => $nombreCompleto,
                    'correo'          => $correo,
                    'password'        => $password, // El modelo encripta solo si viene no vacío
                    'id_rol'          => $idRol,
                    'id_area'         => $idArea,
                ]);

                if ($actualizado) {
                    try {
                        $bitacora = new BitacoraModel($db);
                        $bitacora->registrar(
                            (int)AuthMiddleware::idUsuario(),
                            'usuarios',
                            $idUsuario,
                            'EDITAR',
                            "Actualización de usuario #{$idUsuario}: {$nombreCompleto}"
                        );
                    } catch (Throwable $e) {
                        error_log('Error bitácora edición usuario: ' . $e->getMessage());
                    }

                    $_SESSION['alerta'] = [
                        'tipo' => 'success',
                        'mensaje' => 'Usuario actualizado exitosamente.'
                    ];
                } else {
                    throw new Exception('Ocurrió un error al intentar actualizar el usuario.');
                }
            } catch (Exception $e) {
                $_SESSION['alerta'] = [
                    'tipo' => 'danger',
                    'mensaje' => $e->getMessage()
                ];
            }

            $destino = $_SERVER['HTTP_REFERER'] ?? 'index.php?action=usuarios';
            header('Location: ' . $destino);
            exit();
        }

        header('Location: index.php?action=usuarios');
        exit();
    }

    /**
     * Ejecuta el borrado lógico capturando el ID desde la URL ($_GET['id'] o $_POST).
     */
    public function delete(): void
    {
        AuthMiddleware::verificarSesion();
        RoleMiddleware::requerirRol(RoleMiddleware::ROL_ADMINISTRADOR);

        $idUsuario = (int)($_GET['id'] ?? $_POST['id_usuario'] ?? 0);

        if ($idUsuario <= 0) {
            $_SESSION['alerta'] = [
                'tipo' => 'danger',
                'mensaje' => 'Identificador de usuario inválido para la baja.'
            ];
            $destino = $_SERVER['HTTP_REFERER'] ?? 'index.php?action=usuarios';
            header('Location: ' . $destino);
            exit();
        }

        // Impedir que el administrador activo se elimine a sí mismo
        if ($idUsuario === (int)AuthMiddleware::idUsuario()) {
            $_SESSION['alerta'] = [
                'tipo' => 'danger',
                'mensaje' => 'Acción denegada: no puede dar de baja su propia cuenta de usuario.'
            ];
            $destino = $_SERVER['HTTP_REFERER'] ?? 'index.php?action=usuarios';
            header('Location: ' . $destino);
            exit();
        }

        try {
            $db           = Database::getConnection();
            $usuarioModel = new UsuarioModel($db);
            $usuarioEliminado = $usuarioModel->obtenerPorId($idUsuario);

            if (!$usuarioEliminado) {
                throw new Exception('El usuario que intenta dar de baja no existe o ya está inactivo.');
            }

            $exito = $usuarioModel->eliminar($idUsuario);

            if ($exito) {
                try {
                    $bitacora = new BitacoraModel($db);
                    $nombreEliminado = $usuarioEliminado['nombre_completo'] ?? "ID #{$idUsuario}";
                    $bitacora->registrar(
                        (int)AuthMiddleware::idUsuario(),
                        'usuarios',
                        $idUsuario,
                        'ELIMINAR',
                        "Baja lógica (estatus = 0) del usuario: {$nombreEliminado}"
                    );
                } catch (Throwable $e) {
                    error_log('Error bitácora baja usuario: ' . $e->getMessage());
                }

                $_SESSION['alerta'] = [
                    'tipo' => 'success',
                    'mensaje' => 'Usuario dado de baja exitosamente.'
                ];
            } else {
                throw new Exception('No fue posible completar la baja del usuario.');
            }
        } catch (Exception $e) {
            $_SESSION['alerta'] = [
                'tipo' => 'danger',
                'mensaje' => $e->getMessage()
            ];
        }

        $destino = $_SERVER['HTTP_REFERER'] ?? 'index.php?action=usuarios';
        header('Location: ' . $destino);
        exit();
    }

    public function editar(): void
    {
        $this->update();
    }

    public function eliminar(): void
    {
        $this->delete();
    }
}