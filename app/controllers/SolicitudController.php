<?php

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/RoleMiddleware.php';
require_once __DIR__ . '/../models/SolicitudModel.php';
require_once __DIR__ . '/../models/MotivoModel.php';
require_once __DIR__ . '/../models/VehiculoModel.php';
require_once __DIR__ . '/../models/UsuarioModel.php';
require_once __DIR__ . '/../models/BitacoraModel.php';

/**
 * SolicitudController.php
 * Controlador para la emisión y seguimiento de solicitudes de vehículos
 * con filtrado según el rol (Administrador, Encargado, Solicitante o Jefe de Área).
 */
class SolicitudController
{
    public function index(): void
    {
        AuthMiddleware::verificarSesion();

        // En Fase 2 la vista de solicitudes se consolida:
        // Solicitante gestiona en su Dashboard; Administración en Evaluar Solicitudes.
        if (RoleMiddleware::esSolicitante()) {
            header('Location: index.php?action=dashboard');
            exit();
        }

        header('Location: index.php?action=solicitudes_evaluar');
        exit();
    }

    /**
     * Bandeja de evaluación administrativa para aprobación/asignación,
     * seguimiento de comisiones activas/en ruta e histórico general unificado.
     */
    public function evaluar(): void
    {
        AuthMiddleware::verificarSesion();
        RoleMiddleware::requerirRol([
            RoleMiddleware::ROL_ADMINISTRADOR,
            RoleMiddleware::ROL_JEFE_AREA
        ]);

        $solicitudesPendientes = [];
        $solicitudesActivas    = [];
        $solicitudesHistorico  = [];
        $vehiculosDisponibles  = [];

        try {
            $db = Database::getConnection();
            $solicitudModel = new SolicitudModel($db);
            $vehiculoModel  = new VehiculoModel($db);

            $idRol  = (int)AuthMiddleware::idRol();
            $idArea = (int)AuthMiddleware::idArea();
            $filtroArea = ($idRol === RoleMiddleware::ROL_JEFE_AREA) ? $idArea : null;

            // 1. Solicitudes en Espera / Pendientes
            $solicitudesPendientes = $solicitudModel->obtenerPendientes($filtroArea);

            // 2. Solicitudes Autorizadas / En Ruta
            $solicitudesActivas    = $solicitudModel->obtenerAutorizadasEnRuta($filtroArea);

            // 3. Histórico (Concluidas, Rechazadas, Canceladas)
            $solicitudesHistorico  = $solicitudModel->obtenerHistorico($filtroArea);

            // Unidades disponibles para asignación
            $vehiculosDisponibles  = $vehiculoModel->obtenerDisponibles();
        } catch (Throwable $e) {
            error_log('Error en SolicitudController::evaluar(): ' . $e->getMessage());
        }

        $tituloPagina = 'Evaluación y Gestión de Solicitudes';
        $paginaActual = 'solicitudes_evaluar';

        require_once __DIR__ . '/../views/solicitudes/evaluar.php';
    }

    public function pendientes(): void
    {
        $this->evaluar();
    }

    public function store(): void
    {
        AuthMiddleware::verificarSesion();
        RoleMiddleware::requerirRol([
            RoleMiddleware::ROL_ADMINISTRADOR,
            RoleMiddleware::ROL_SOLICITANTE,
            RoleMiddleware::ROL_JEFE_AREA
        ]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idUsuario = (int)AuthMiddleware::idUsuario();
            $esSolicitante = RoleMiddleware::esSolicitante();
            $urlFallback = $esSolicitante ? 'index.php?action=dashboard' : 'index.php?action=solicitudes_evaluar';
            $urlDestino = $_SERVER['HTTP_REFERER'] ?? $urlFallback;

            try {
                $db = Database::getConnection();

                // ── 1. Validación Previa de Licencia de Conducir ─────────────
                $usuarioModel = new UsuarioModel($db);
                $licencia = $usuarioModel->obtenerLicencia($idUsuario);

                $numLicencia = trim($licencia['numero_licencia'] ?? '');
                $vigencia    = trim($licencia['vigencia_licencia'] ?? '');
                /* --- INICIO DE BLOQUE DESACTIVADO PARA PRUEBAS ---
                if (empty($numLicencia) || empty($vigencia)) {
                    $_SESSION['alerta'] = [
                        'tipo' => 'warning',
                        'mensaje' => 'No es posible registrar la solicitud: No cuenta con una Licencia de Conducir registrada en su expediente. Favor de acudir con el área administrativa para actualizar su acreditación.'
                    ];
                    header('Location: ' . $urlDestino);
                    exit();
                }

                if (strtotime($vigencia) < strtotime('today')) {
                    $fechaFormateada = date('d/m/Y', strtotime($vigencia));
                    $_SESSION['alerta'] = [
                        'tipo' => 'danger',
                        'mensaje' => "No es posible registrar la solicitud: Su Licencia de Conducir (#{$numLicencia}) se encuentra vencida desde el {$fechaFormateada}. Debe renovar y registrar su acreditación vigente para solicitar comisiones oficiales."
                    ];
                    header('Location: ' . $urlDestino);
                    exit();
                }
                --- FIN DE BLOQUE DESACTIVADO PARA PRUEBAS --- */
                // ── 2. Captura y Sanitización de Campos Logísticos ────────────
                $idMotivo       = (int)($_POST['id_motivo'] ?? 0);
                $especificacion = trim($_POST['especificacion_motivo'] ?? '');
                $destino        = trim($_POST['destino'] ?? '');
                $tipoComision   = trim($_POST['tipo_comision'] ?? 'Local');
                $fechaRequerida = trim($_POST['fecha_requerida'] ?? '');
                $horaRequerida  = trim($_POST['hora_requerida'] ?? '');
                $fechaRetorno   = trim($_POST['fecha_retorno'] ?? '');
                $horaRetorno    = trim($_POST['hora_retorno'] ?? '');
                $numPasajeros   = max(1, (int)($_POST['num_pasajeros'] ?? 1));

                // Normalizar tipo de comisión
                if (!in_array($tipoComision, ['Local', 'Foránea', 'Foranea'], true)) {
                    $tipoComision = 'Local';
                }

                if ($idMotivo <= 0 || empty($destino) || empty($fechaRequerida) || empty($horaRequerida)) {
                    throw new Exception('Complete todos los campos obligatorios para registrar la solicitud (Motivo, destino, fecha y hora de salida).');
                }

                // Simplificación de Retorno: Si el usuario no ingresa fecha_retorno u hora_retorno,
                // se le asigna por defecto la misma fecha_requerida de salida y una duración estimada estándar (+2 horas).
                if (empty($fechaRetorno)) {
                    $fechaRetorno = $fechaRequerida;
                }
                if (empty($horaRetorno)) {
                    $tsSalida = strtotime("{$fechaRequerida} {$horaRequerida}");
                    if ($tsSalida !== false) {
                        $tsEstimado = $tsSalida + 7200; // +2 horas por defecto
                        $horaRetorno = date('H:i', $tsEstimado);
                        if (date('Y-m-d', $tsEstimado) !== $fechaRequerida) {
                            $fechaRetorno = date('Y-m-d', $tsEstimado);
                        }
                    } else {
                        $horaRetorno = '18:00';
                    }
                }

                // Validar coherencia temporal entre salida y retorno
                $timestampSalida  = strtotime("{$fechaRequerida} {$horaRequerida}");
                $timestampRetorno = strtotime("{$fechaRetorno} {$horaRetorno}");

                if ($timestampRetorno <= $timestampSalida) {
                    $timestampRetorno = $timestampSalida + 7200;
                    $fechaRetorno = date('Y-m-d', $timestampRetorno);
                    $horaRetorno  = date('H:i', $timestampRetorno);
                }

                // ── 3. Procesamiento de Itinerario y Paradas Intermedias ───────
                $itinerarioParadasJson = null;
                if (!empty($_POST['paradas']) && is_array($_POST['paradas'])) {
                    $paradasLimpias = [];
                    foreach ($_POST['paradas'] as $parada) {
                        if (!is_array($parada)) continue;
                        $ubicacion    = trim($parada['ubicacion'] ?? '');
                        $motivoParada = trim($parada['motivo'] ?? '');
                        if (!empty($ubicacion)) {
                            $paradasLimpias[] = [
                                'ubicacion' => $ubicacion,
                                'motivo'    => $motivoParada
                            ];
                        }
                    }
                    if (!empty($paradasLimpias)) {
                        $itinerarioParadasJson = json_encode($paradasLimpias, JSON_UNESCAPED_UNICODE);
                    }
                }

                // ── 4. Creación Delegada a SolicitudModel (Cero selección vehicular) ─
                $solicitudModel = new SolicitudModel($db);
                $idSolicitud = $solicitudModel->crear([
                    'id_usuario_solicitante' => $idUsuario,
                    'id_motivo'              => $idMotivo,
                    'especificacion_motivo'  => $especificacion ?: null,
                    'itinerario_paradas'     => $itinerarioParadasJson,
                    'destino'                => $destino,
                    'tipo_comision'          => $tipoComision,
                    'fecha_requerida'        => $fechaRequerida,
                    'hora_requerida'         => $horaRequerida,
                    'fecha_retorno'          => $fechaRetorno,
                    'hora_retorno'           => $horaRetorno,
                    'num_pasajeros'          => $numPasajeros,
                ]);

                if ($idSolicitud > 0) {
                    // Registro de auditoría
                    try {
                        $bitacora = new BitacoraModel($db);
                        $bitacora->registrar(
                            $idUsuario,
                            'solicitudes',
                            $idSolicitud,
                            'CREAR',
                            "Solicitud de comisión #{$idSolicitud} registrada. Destino: {$destino} ({$tipoComision}), Salida: {$fechaRequerida} {$horaRequerida}, Retorno: {$fechaRetorno} {$horaRetorno}, Pasajeros: {$numPasajeros}."
                        );
                    } catch (Throwable $e) {
                        error_log('Error creando bitácora de solicitud: ' . $e->getMessage());
                    }

                    $_SESSION['alerta'] = [
                        'tipo' => 'success',
                        'mensaje' => "Solicitud #{$idSolicitud} registrada exitosamente en estado 'Pendiente'. El vehículo oficial será asignado por Administración conforme a la disponibilidad del parque vehicular."
                    ];

                    $urlExito = $esSolicitante ? 'index.php?action=dashboard' : 'index.php?action=solicitudes_evaluar';
                    header('Location: ' . $urlExito);
                    exit();
                }

                throw new Exception('No fue posible guardar la solicitud. Intente nuevamente.');
            } catch (Exception $e) {
                $_SESSION['alerta'] = [
                    'tipo' => 'danger',
                    'mensaje' => $e->getMessage()
                ];
                header('Location: ' . $urlDestino);
                exit();
            }
        }

        header('Location: index.php?action=solicitudes_evaluar');
        exit();
    }

    /**
     * Aprueba una solicitud en estado 'Pendiente' con asignación vehicular obligatoria.
     * Exclusivo para usuarios con rol de Administración o Encargado Vehicular.
     */
    public function aprobar(): void
    {
        AuthMiddleware::verificarSesion();
        RoleMiddleware::requerirRol([
            RoleMiddleware::ROL_ADMINISTRADOR,
            RoleMiddleware::ROL_JEFE_AREA
        ]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idSolicitud = (int)($_POST['id_solicitud'] ?? 0);
            $idVehiculo  = (int)($_POST['id_vehiculo'] ?? 0);
            $comentarios = trim($_POST['comentarios_jefe'] ?? $_POST['comentarios'] ?? '');

            $urlDestino = $_SERVER['HTTP_REFERER'] ?? 'index.php?action=solicitudes_evaluar';

            if ($idSolicitud <= 0) {
                $_SESSION['alerta'] = [
                    'tipo' => 'danger',
                    'mensaje' => 'Folio de solicitud no válido.'
                ];
                header('Location: ' . $urlDestino);
                exit();
            }

            if ($idVehiculo <= 0) {
                $_SESSION['alerta'] = [
                    'tipo' => 'danger',
                    'mensaje' => 'Para autorizar la solicitud es estrictamente obligatorio seleccionar una unidad disponible del parque vehicular.'
                ];
                header('Location: ' . $urlDestino);
                exit();
            }

            try {
                $db = Database::getConnection();
                $solicitudModel = new SolicitudModel($db);
                $vehiculoModel  = new VehiculoModel($db);

                // 1. Verificar existencia y estado 'Pendiente' de la solicitud
                $solicitud = $solicitudModel->obtenerPorId($idSolicitud);
                if (!$solicitud) {
                    throw new Exception("La solicitud #{$idSolicitud} no existe.");
                }
                if (($solicitud['estado_solicitud'] ?? '') !== 'Pendiente') {
                    throw new Exception("La solicitud #{$idSolicitud} no se encuentra en estado 'Pendiente' (estado actual: {$solicitud['estado_solicitud']}).");
                }

                // 2. Verificar que el vehículo seleccionado exista y esté disponible
                $vehiculo = $vehiculoModel->obtenerPorId($idVehiculo);
                if (!$vehiculo || ($vehiculo['estado_operativo'] ?? '') !== 'Disponible' || (int)($vehiculo['estatus'] ?? 1) !== 1) {
                    throw new Exception('El vehículo seleccionado no se encuentra disponible para asignación.');
                }

                // 3. Asignar unidad y cambiar estatus a 'Autorizada'
                $idEvaluador = (int)AuthMiddleware::idUsuario();
                $ok = $solicitudModel->asignarVehiculo($idSolicitud, $idVehiculo, $idEvaluador, $comentarios ?: null);

                if ($ok) {
                    // 4. Registro detallado en bitácora de auditoría
                    try {
                        $bitacora = new BitacoraModel($db);
                        $eco = $vehiculo['numero_economico'] ?? 'S/N';
                        $marcaMod = trim(($vehiculo['marca'] ?? '') . ' ' . ($vehiculo['modelo'] ?? ''));
                        $placas = $vehiculo['placas'] ?? '';
                        $descBitacora = "Aprobación de solicitud #{$idSolicitud} con asignación de unidad Eco: {$eco} ({$marcaMod}, Placas: {$placas}). Solicitante: {$solicitud['solicitante_nombre']}.";
                        if (!empty($comentarios)) {
                            $descBitacora .= " Observaciones: {$comentarios}";
                        }
                        $bitacora->registrar(
                            $idEvaluador,
                            'solicitudes',
                            $idSolicitud,
                            'AUTORIZAR',
                            $descBitacora
                        );
                    } catch (Throwable $e) {
                        error_log('Error registrando bitácora al aprobar solicitud: ' . $e->getMessage());
                    }

                    $_SESSION['alerta'] = [
                        'tipo' => 'success',
                        'mensaje' => "Solicitud #{$idSolicitud} autorizada exitosamente. Vehículo asignado: Eco #{$vehiculo['numero_economico']} — {$vehiculo['marca']} {$vehiculo['modelo']} (Placas: {$vehiculo['placas']})."
                    ];
                } else {
                    throw new Exception('No fue posible actualizar el estado de la solicitud. Intente nuevamente.');
                }
            } catch (Exception $e) {
                $_SESSION['alerta'] = [
                    'tipo' => 'danger',
                    'mensaje' => $e->getMessage()
                ];
            }

            header('Location: ' . $urlDestino);
            exit();
        }

        header('Location: index.php?action=solicitudes_evaluar');
        exit();
    }

    /**
     * Alias de aprobar() para retrocompatibilidad de rutas.
     */
    public function autorizar(): void
    {
        $this->aprobar();
    }

    /**
     * Rechaza una solicitud en estado 'Pendiente' registrando la justificación en auditoría.
     * Exclusivo para usuarios con rol de Administración o Encargado Vehicular.
     */
    public function rechazar(): void
    {
        AuthMiddleware::verificarSesion();
        RoleMiddleware::requerirRol([
            RoleMiddleware::ROL_ADMINISTRADOR,
            RoleMiddleware::ROL_JEFE_AREA
        ]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idSolicitud = (int)($_POST['id_solicitud'] ?? 0);
            $comentarios = trim($_POST['comentarios_jefe'] ?? $_POST['motivo_rechazo'] ?? $_POST['comentarios'] ?? '');

            $urlDestino = $_SERVER['HTTP_REFERER'] ?? 'index.php?action=solicitudes_evaluar';

            if ($idSolicitud <= 0) {
                $_SESSION['alerta'] = [
                    'tipo' => 'danger',
                    'mensaje' => 'Folio de solicitud no válido.'
                ];
                header('Location: ' . $urlDestino);
                exit();
            }

            try {
                $db = Database::getConnection();
                $solicitudModel = new SolicitudModel($db);

                // 1. Verificar existencia y estado 'Pendiente' de la solicitud
                $solicitud = $solicitudModel->obtenerPorId($idSolicitud);
                if (!$solicitud) {
                    throw new Exception("La solicitud #{$idSolicitud} no existe.");
                }
                if (($solicitud['estado_solicitud'] ?? '') !== 'Pendiente') {
                    throw new Exception("La solicitud #{$idSolicitud} no se encuentra en estado 'Pendiente' (estado actual: {$solicitud['estado_solicitud']}).");
                }

                // 2. Ejecutar rechazo registrando la justificación
                $idEvaluador = (int)AuthMiddleware::idUsuario();
                $ok = $solicitudModel->rechazar($idSolicitud, $idEvaluador, $comentarios ?: null);

                if ($ok) {
                    // 3. Registro detallado en bitácora de auditoría
                    try {
                        $bitacora = new BitacoraModel($db);
                        $descBitacora = "Rechazo de solicitud de comisión #{$idSolicitud} del solicitante {$solicitud['solicitante_nombre']}.";
                        if (!empty($comentarios)) {
                            $descBitacora .= " Motivo/Justificación: {$comentarios}";
                        }
                        $bitacora->registrar(
                            $idEvaluador,
                            'solicitudes',
                            $idSolicitud,
                            'RECHAZAR',
                            $descBitacora
                        );
                    } catch (Throwable $e) {
                        error_log('Error registrando bitácora al rechazar solicitud: ' . $e->getMessage());
                    }

                    $_SESSION['alerta'] = [
                        'tipo' => 'success',
                        'mensaje' => "La solicitud #{$idSolicitud} ha sido rechazada exitosamente."
                    ];
                } else {
                    throw new Exception('No fue posible rechazar la solicitud. Intente nuevamente.');
                }
            } catch (Exception $e) {
                $_SESSION['alerta'] = [
                    'tipo' => 'danger',
                    'mensaje' => $e->getMessage()
                ];
            }

            header('Location: ' . $urlDestino);
            exit();
        }

        header('Location: index.php?action=solicitudes_evaluar');
        exit();
    }

    /**
     * Cancela una solicitud de vehículo.
     * Exclusivo para solicitudes en estado 'Pendiente' y pertenecientes al solicitante en sesión.
     */
    public function cancelar(): void
    {
        AuthMiddleware::verificarSesion();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['id'])) {
            $idSolicitud = (int)($_POST['id_solicitud'] ?? $_GET['id'] ?? 0);
            $idUsuario   = (int)AuthMiddleware::idUsuario();
            $idRol       = (int)AuthMiddleware::idRol();

            if ($idSolicitud <= 0) {
                $_SESSION['alerta'] = [
                    'tipo' => 'danger',
                    'mensaje' => 'Identificador de solicitud inválido.'
                ];
                $urlDestino = $_SERVER['HTTP_REFERER'] ?? 'index.php?action=dashboard';
                header('Location: ' . $urlDestino);
                exit();
            }

            try {
                $db     = Database::getConnection();
                $modelo = new SolicitudModel($db);

                $solicitud = $modelo->obtenerPorId($idSolicitud);
                if (!$solicitud) {
                    throw new Exception('La solicitud especificada no existe.');
                }

                // Si es rol solicitante, sólo puede cancelar sus propias solicitudes
                if ($idRol === RoleMiddleware::ROL_SOLICITANTE && (int)$solicitud['id_usuario_solicitante'] !== $idUsuario) {
                    throw new Exception('No tiene permisos para cancelar esta solicitud.');
                }

                if (($solicitud['estado_solicitud'] ?? '') !== 'Pendiente') {
                    throw new Exception('Únicamente se pueden cancelar solicitudes en estado "Pendiente".');
                }

                $exito = $modelo->cancelar($idSolicitud, (int)$solicitud['id_usuario_solicitante']);

                if ($exito) {
                    try {
                        $bitacora = new BitacoraModel($db);
                        $bitacora->registrar(
                            $idUsuario,
                            'solicitudes',
                            $idSolicitud,
                            'CANCELAR',
                            "Cancelación de solicitud #{$idSolicitud} por parte del solicitante."
                        );
                    } catch (Throwable $e) {
                        error_log('Error en bitácora al cancelar solicitud: ' . $e->getMessage());
                    }

                    $_SESSION['alerta'] = [
                        'tipo' => 'success',
                        'mensaje' => "La solicitud #{$idSolicitud} fue cancelada correctamente."
                    ];
                } else {
                    throw new Exception('No fue posible cancelar la solicitud.');
                }
            } catch (Exception $e) {
                $_SESSION['alerta'] = [
                    'tipo' => 'danger',
                    'mensaje' => $e->getMessage()
                ];
            }

            $urlDestino = $_SERVER['HTTP_REFERER'] ?? 'index.php?action=dashboard';
            header('Location: ' . $urlDestino);
            exit();
        }

        header('Location: index.php?action=dashboard');
        exit();
    }
}
