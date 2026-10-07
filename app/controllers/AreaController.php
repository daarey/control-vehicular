<?php

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/RoleMiddleware.php';
require_once __DIR__ . '/../models/AreaModel.php';
require_once __DIR__ . '/../models/BitacoraModel.php';

/**
 * AreaController.php
 * Controlador para la administración del catálogo de áreas institucionales.
 */
class AreaController
{
    public function index(): void
    {
        AuthMiddleware::verificarSesion();
        RoleMiddleware::requerirRol(RoleMiddleware::ROL_ADMINISTRADOR);

        $areas = [];
        try {
            $db = Database::getConnection();
            $modelo = new AreaModel($db);
            $areas = $modelo->obtenerTodas();
        } catch (Throwable $e) {
            error_log('Error en AreaController: ' . $e->getMessage());
        }

        $tituloPagina = 'Áreas Institucionales';
        $paginaActual = 'areas';

        require_once __DIR__ . '/../views/areas/index.php';
    }

    public function store(): void
    {
        AuthMiddleware::verificarSesion();
        RoleMiddleware::requerirRol(RoleMiddleware::ROL_ADMINISTRADOR);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombreArea = trim($_POST['nombre_area'] ?? '');

            if (empty($nombreArea)) {
                $_SESSION['alerta'] = [
                    'tipo' => 'danger',
                    'mensaje' => 'El nombre del área no puede estar vacío.'
                ];
                $destino = $_SERVER['HTTP_REFERER'] ?? 'index.php?action=areas';
                header('Location: ' . $destino);
                exit();
            }

            try {
                $db = Database::getConnection();
                $modelo = new AreaModel($db);
                $creado = $modelo->crearArea($nombreArea);

                if ($creado) {
                    try {
                        $bitacora = new BitacoraModel($db);
                        $bitacora->registrar(
                            (int)AuthMiddleware::idUsuario(),
                            'areas',
                            0,
                            'CREAR',
                            "Registro de nueva área: {$nombreArea}"
                        );
                    } catch (Throwable $e) {
                        error_log('Error en bitácora AreaController: ' . $e->getMessage());
                    }

                    $_SESSION['alerta'] = [
                        'tipo' => 'success',
                        'mensaje' => 'Registro completado exitosamente.'
                    ];
                    header('Location: index.php?action=areas');
                    exit();
                }

                throw new Exception('No se pudo crear el área institucional.');
            } catch (Exception $e) {
                $_SESSION['alerta'] = [
                    'tipo' => 'danger',
                    'mensaje' => $e->getMessage()
                ];
                $destino = $_SERVER['HTTP_REFERER'] ?? 'index.php?action=areas';
                header('Location: ' . $destino);
                exit();
            }
        }

        header('Location: index.php?action=areas');
        exit();
    }

    public function editar(): void
    {
        AuthMiddleware::verificarSesion();
        RoleMiddleware::requerirRol(RoleMiddleware::ROL_ADMINISTRADOR);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idArea = (int)($_POST['id_area'] ?? 0);
            $nombreArea = trim($_POST['nombre_area'] ?? '');

            if ($idArea <= 0 || empty($nombreArea)) {
                $_SESSION['alerta'] = [
                    'tipo' => 'danger',
                    'mensaje' => 'El identificador y el nombre del área son obligatorios.'
                ];
                $destino = $_SERVER['HTTP_REFERER'] ?? 'index.php?action=areas';
                header('Location: ' . $destino);
                exit();
            }

            try {
                $db = Database::getConnection();
                $modelo = new AreaModel($db);
                $actualizado = $modelo->editarArea($idArea, ['nombre_area' => $nombreArea]);

                if ($actualizado) {
                    try {
                        $bitacora = new BitacoraModel($db);
                        $bitacora->registrar(
                            (int)AuthMiddleware::idUsuario(),
                            'areas',
                            $idArea,
                            'ACTUALIZAR',
                            "Actualización de área #{$idArea}: {$nombreArea}"
                        );
                    } catch (Throwable $e) {
                        error_log('Error en bitácora AreaController: ' . $e->getMessage());
                    }

                    $_SESSION['alerta'] = [
                        'tipo' => 'success',
                        'mensaje' => 'Área institucional actualizada exitosamente.'
                    ];
                    header('Location: index.php?action=areas');
                    exit();
                }

                throw new Exception('No se pudo actualizar el área institucional.');
            } catch (Exception $e) {
                $_SESSION['alerta'] = [
                    'tipo' => 'danger',
                    'mensaje' => $e->getMessage()
                ];
                $destino = $_SERVER['HTTP_REFERER'] ?? 'index.php?action=areas';
                header('Location: ' . $destino);
                exit();
            }
        }

        header('Location: index.php?action=areas');
        exit();
    }

    public function eliminar(): void
    {
        AuthMiddleware::verificarSesion();
        RoleMiddleware::requerirRol(RoleMiddleware::ROL_ADMINISTRADOR);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idArea = (int)($_POST['id_area'] ?? 0);

            if ($idArea <= 0) {
                $_SESSION['alerta'] = [
                    'tipo' => 'danger',
                    'mensaje' => 'Identificador de área no válido.'
                ];
                header('Location: index.php?action=areas');
                exit();
            }

            try {
                $db = Database::getConnection();
                $modelo = new AreaModel($db);
                $eliminado = $modelo->eliminarArea($idArea);

                if ($eliminado) {
                    try {
                        $bitacora = new BitacoraModel($db);
                        $bitacora->registrar(
                            (int)AuthMiddleware::idUsuario(),
                            'areas',
                            $idArea,
                            'ELIMINAR',
                            "Eliminación de área institucional #{$idArea}"
                        );
                    } catch (Throwable $e) {
                        error_log('Error en bitácora AreaController: ' . $e->getMessage());
                    }

                    $_SESSION['alerta'] = [
                        'tipo' => 'success',
                        'mensaje' => 'Área eliminada exitosamente.'
                    ];
                    header('Location: index.php?action=areas');
                    exit();
                }

                throw new Exception('No se pudo eliminar el área institucional.');
            } catch (Exception $e) {
                $_SESSION['alerta'] = [
                    'tipo' => 'danger',
                    'mensaje' => $e->getMessage()
                ];
                header('Location: index.php?action=areas');
                exit();
            }
        }

        header('Location: index.php?action=areas');
        exit();
    }
}
