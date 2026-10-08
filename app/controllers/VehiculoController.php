<?php

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/RoleMiddleware.php';
require_once __DIR__ . '/../models/VehiculoModel.php';
require_once __DIR__ . '/../models/UsuarioModel.php';
require_once __DIR__ . '/../models/BitacoraModel.php';

/**
 * VehiculoController.php
 * Controlador para la gestión y consulta del parque vehicular.
 */
class VehiculoController
{
    private $modeloVehiculo;

    public function __construct($conexion = null)
    {
        if ($conexion === null) {
            $conexion = Database::getConnection();
        }
        $this->modeloVehiculo = new VehiculoModel($conexion);
    }

    // Cargar la vista principal con filtros
    public function index()
    {
        AuthMiddleware::verificarSesion();
        RoleMiddleware::requerirRol([
            RoleMiddleware::ROL_ADMINISTRADOR,
            RoleMiddleware::ROL_JEFE_AREA
        ]);

        $orden = $_GET['orden'] ?? 'numero_economico';
        $dir = $_GET['dir'] ?? 'DESC';
        $estado = $_GET['estado'] ?? null;
        $busqueda = trim($_GET['busqueda'] ?? $_GET['q'] ?? '');
        $pagina = max(1, (int)($_GET['pagina'] ?? $_GET['page'] ?? 1));
        $porPagina = 10;
        $offset = ($pagina - 1) * $porPagina;

        // Invertir dirección para el próximo clic en la tabla
        $nuevoDir = ($dir === 'ASC') ? 'DESC' : 'ASC'; 

        $totalRegistros = $this->modeloVehiculo->contarTotal($busqueda, $estado);
        $totalPaginas = (int)ceil($totalRegistros / $porPagina);
        if ($totalPaginas < 1) {
            $totalPaginas = 1;
        }

        if ($pagina > $totalPaginas && $totalRegistros > 0) {
            $pagina = $totalPaginas;
            $offset = ($pagina - 1) * $porPagina;
        }

        $vehiculos = $this->modeloVehiculo->obtenerPaginados($porPagina, $offset, $busqueda, $estado, $orden, $dir);


        // Cargar catálogo de usuarios para los formularios
        $usuarios = [];
        try {
            $db = Database::getConnection();
            $usuarioModel = new UsuarioModel($db);
            $usuarios = $usuarioModel->obtenerTodos();
        } catch (Throwable $e) {
            error_log('Error obteniendo usuarios en VehiculoController: ' . $e->getMessage());
        }

        // Manejo de mensajes de retroalimentación
        $mensaje = $_GET['mensaje'] ?? null;
        if ($mensaje === 'exito') {
            $_SESSION['alerta'] = ['tipo' => 'success', 'mensaje' => 'Vehículo registrado exitosamente en el sistema.'];
        } elseif ($mensaje === 'error') {
            if (!isset($_SESSION['alerta'])) {
                $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'Ocurrió un error al intentar registrar el vehículo.'];
            }
        }

        $tituloPagina = 'Gestión de Vehículos';
        $paginaActual = 'vehiculos';

        if (file_exists('app/views/vehiculos/index.php')) {
            require 'app/views/vehiculos/index.php';
        } else {
            require __DIR__ . '/../views/vehiculos/index.php';
        }
    }

    // Procesar el formulario de registro
    public function store()
    {
        AuthMiddleware::verificarSesion();
        RoleMiddleware::requerirRol(RoleMiddleware::ROL_ADMINISTRADOR);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $numeroEconomico = trim($_POST['numero_economico'] ?? '');
            $placas          = trim($_POST['placas'] ?? '');
            $marca           = trim($_POST['marca'] ?? '');
            $modelo          = trim($_POST['modelo'] ?? '');
            $modeloAnio      = trim($_POST['modelo_anio'] ?? '');

            if (empty($numeroEconomico) || empty($placas) || empty($marca) || empty($modelo) || empty($modeloAnio)) {
                $_SESSION['alerta'] = [
                    'tipo' => 'danger',
                    'mensaje' => 'Por favor complete todos los campos obligatorios del vehículo (Número económico, placas, marca, modelo y año).'
                ];
                $destino = $_SERVER['HTTP_REFERER'] ?? 'index.php?action=vehiculos';
                header('Location: ' . $destino);
                exit();
            }

            try {
                $registroExitoso = $this->modeloVehiculo->registrar($_POST);
                
                if ($registroExitoso) {
                    try {
                        $db = Database::getConnection();
                        $bitacora = new BitacoraModel($db);
                        $idUsuario = (int)AuthMiddleware::idUsuario();
                        $numEco = htmlspecialchars($numeroEconomico, ENT_QUOTES, 'UTF-8');
                        $marcaEsc = htmlspecialchars($marca, ENT_QUOTES, 'UTF-8');
                        $modEsc = htmlspecialchars($modelo, ENT_QUOTES, 'UTF-8');
                        $placasEsc = htmlspecialchars($placas, ENT_QUOTES, 'UTF-8');
                        $bitacora->registrar(
                            $idUsuario,
                            'vehiculos',
                            0,
                            'CREAR',
                            "Registro de nuevo vehículo #{$numEco} ({$marcaEsc} {$modEsc} - {$placasEsc})"
                        );
                    } catch (Throwable $e) {
                        error_log('Error registrando bitácora de nuevo vehículo: ' . $e->getMessage());
                    }

                    $_SESSION['alerta'] = [
                        'tipo' => 'success',
                        'mensaje' => 'Registro completado exitosamente.'
                    ];
                    header("Location: index.php?action=vehiculos");
                    exit();
                } else {
                    throw new Exception('Ocurrió un error inesperado al intentar registrar el vehículo.');
                }
            } catch (Exception $e) {
                $_SESSION['alerta'] = [
                    'tipo' => 'danger',
                    'mensaje' => $e->getMessage()
                ];
                $destino = $_SERVER['HTTP_REFERER'] ?? 'index.php?action=vehiculos';
                header('Location: ' . $destino);
                exit();
            }
        }

        header('Location: index.php?action=vehiculos');
        exit();
    }

    public function editar(): void
    {
        AuthMiddleware::verificarSesion();
        RoleMiddleware::requerirRol(RoleMiddleware::ROL_ADMINISTRADOR);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id                = (int)($_POST['id_vehiculo'] ?? 0);
            $numeroEconomico   = trim($_POST['numero_economico'] ?? '');
            $placas            = strtoupper(trim($_POST['placas'] ?? ''));
            $marca             = trim($_POST['marca'] ?? '');
            $modelo            = trim($_POST['modelo'] ?? '');
            $modeloAnio        = (int)($_POST['modelo_anio'] ?? date('Y'));
            $numeroPatrimonial = trim($_POST['numero_patrimonial'] ?? '');
            $numeroSerie       = strtoupper(trim($_POST['numero_serie'] ?? ''));
            $kmActual          = max(0, (int)($_POST['km_actual'] ?? 0));
            $estadoOperativo   = trim($_POST['estado_operativo'] ?? 'Disponible');
            $idResguardante    = (int)($_POST['id_resguardante'] ?? 0);

            $estadosValidos = ['Disponible', 'En ruta', 'En mantenimiento'];
            if (!in_array($estadoOperativo, $estadosValidos, true)) {
                $estadoOperativo = 'Disponible';
            }

            if ($id <= 0 || empty($numeroEconomico) || empty($placas) || empty($marca)) {
                $_SESSION['alerta'] = [
                    'tipo' => 'danger',
                    'mensaje' => 'Por favor complete todos los campos obligatorios del vehículo.'
                ];
                $destino = $_SERVER['HTTP_REFERER'] ?? 'index.php?action=vehiculos';
                header('Location: ' . $destino);
                exit();
            }

            try {
                $vehiculoExistente = $this->modeloVehiculo->obtenerPorId($id);
                if (!$vehiculoExistente) {
                    throw new Exception('El vehículo que intenta editar no existe.');
                }

                $datos = [
                    'numero_economico'   => $numeroEconomico,
                    'placas'             => $placas,
                    'marca'              => $marca,
                    'modelo'             => $modelo,
                    'modelo_anio'        => $modeloAnio,
                    'numero_patrimonial' => $numeroPatrimonial,
                    'numero_serie'       => $numeroSerie,
                    'km_actual'          => $kmActual,
                    'estado_operativo'   => $estadoOperativo,
                    'id_resguardante'    => $idResguardante
                ];

                $actualizado = $this->modeloVehiculo->editarVehiculo($id, $datos);

                if ($actualizado) {
                    try {
                        $db = Database::getConnection();
                        $bitacora = new BitacoraModel($db);
                        $idUsuario = (int)AuthMiddleware::idUsuario();
                        $bitacora->registrar(
                            $idUsuario,
                            'vehiculos',
                            $id,
                            'EDITAR',
                            "Actualización de vehículo #{$numeroEconomico} ({$marca} {$modelo} - {$placas})"
                        );
                    } catch (Throwable $e) {
                        error_log('Error registrando bitácora de edición: ' . $e->getMessage());
                    }

                    $_SESSION['alerta'] = [
                        'tipo' => 'success',
                        'mensaje' => "El vehículo #{$numeroEconomico} ({$placas}) ha sido actualizado exitosamente."
                    ];
                } else {
                    throw new Exception('Ocurrió un error al intentar guardar los cambios del vehículo.');
                }
            } catch (Exception $e) {
                $_SESSION['alerta'] = [
                    'tipo' => 'danger',
                    'mensaje' => $e->getMessage()
                ];
            }

            $destino = $_SERVER['HTTP_REFERER'] ?? 'index.php?action=vehiculos';
            header('Location: ' . $destino);
            exit();
        }

        header('Location: index.php?action=vehiculos');
        exit();
    }

    public function eliminar(): void
    {
        AuthMiddleware::verificarSesion();
        RoleMiddleware::requerirRol(RoleMiddleware::ROL_ADMINISTRADOR);

        $id = (int)($_POST['id_vehiculo'] ?? $_GET['id'] ?? 0);

        if ($id <= 0) {
            $_SESSION['alerta'] = [
                'tipo' => 'danger',
                'mensaje' => 'Identificador de vehículo inválido para la eliminación.'
            ];
            $destino = $_SERVER['HTTP_REFERER'] ?? 'index.php?action=vehiculos';
            header('Location: ' . $destino);
            exit();
        }

        try {
            $vehiculo = $this->modeloVehiculo->obtenerPorId($id);
            if (!$vehiculo) {
                throw new Exception('El vehículo que intenta eliminar no existe o ya fue dado de baja.');
            }

            $eliminado = $this->modeloVehiculo->eliminarVehiculo($id);

            if ($eliminado) {
                try {
                    $db = Database::getConnection();
                    $bitacora = new BitacoraModel($db);
                    $idUsuario = (int)AuthMiddleware::idUsuario();
                    $bitacora->registrar(
                        $idUsuario,
                        'vehiculos',
                        $id,
                        'ELIMINAR',
                        "Baja lógica de vehículo #{$vehiculo['numero_economico']} ({$vehiculo['marca']} {$vehiculo['modelo']} - {$vehiculo['placas']})"
                    );
                } catch (Throwable $e) {
                    error_log('Error registrando bitácora de eliminación: ' . $e->getMessage());
                }

                $_SESSION['alerta'] = [
                    'tipo' => 'success',
                    'mensaje' => "El vehículo #{$vehiculo['numero_economico']} ({$vehiculo['placas']}) ha sido dado de baja exitosamente."
                ];
            } else {
                throw new Exception('No se pudo completar la baja del vehículo.');
            }
        } catch (Exception $e) {
            $_SESSION['alerta'] = [
                'tipo' => 'danger',
                'mensaje' => $e->getMessage()
            ];
        }

        $destino = $_SERVER['HTTP_REFERER'] ?? 'index.php?action=vehiculos';
        header('Location: ' . $destino);
        exit();
    }
}
