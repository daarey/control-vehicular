<?php

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/RoleMiddleware.php';
require_once __DIR__ . '/../models/MovimientoModel.php';
require_once __DIR__ . '/../models/VehiculoModel.php';
require_once __DIR__ . '/../models/ConductorModel.php';
require_once __DIR__ . '/../models/MotivoModel.php';
require_once __DIR__ . '/../models/BitacoraModel.php';
require_once __DIR__ . '/../models/SolicitudModel.php';

/**
 * MovimientoController.php
 * Controlador para la bitácora de entradas, salidas y despacho en caseta.
 */
class MovimientoController
{
    public function index(): void
    {
        AuthMiddleware::verificarSesion();
        RoleMiddleware::requerirRol([
            RoleMiddleware::ROL_ADMINISTRADOR,
            RoleMiddleware::ROL_ENCARGADO_VEHICULAR
        ]);

        $solicitudesAutorizadas = [];
        $vehiculosEnRuta        = [];
        $movimientos            = [];
        $totalVehiculosDisp     = 0;

        try {
            $db            = Database::getConnection();
            $modelo        = new MovimientoModel($db);
            $vehiculoModel = new VehiculoModel($db);

            $solicitudesAutorizadas = $modelo->obtenerSolicitudesListasSalida();
            $vehiculosEnRuta        = $modelo->obtenerVehiculosEnRuta();
            $movimientos            = $modelo->obtenerRecientes(50);
            $totalVehiculosDisp     = count($vehiculoModel->obtenerDisponibles());
        } catch (Throwable $e) {
            error_log('Error en MovimientoController::index(): ' . $e->getMessage());
        }

        $tituloPagina = 'Control de Caseta — Salidas y Retornos';
        $paginaActual = 'movimientos';

        // dashboard/caseta.php es la pantalla única oficial de operación de caseta
        require_once __DIR__ . '/../views/dashboard/caseta.php';
    }


    /**
     * Registra el despacho y salida de una unidad en caseta con transacción SQL atómica.
     * Soporta salidas ligadas a comisiones oficiales autorizadas o salidas de guardia directas.
     */
    public function salida(): void
    {
        AuthMiddleware::verificarSesion();
        RoleMiddleware::requerirRol([
            RoleMiddleware::ROL_ADMINISTRADOR,
            RoleMiddleware::ROL_ENCARGADO_VEHICULAR
        ]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idSolicitud    = !empty($_POST['id_solicitud']) ? (int)$_POST['id_solicitud'] : null;
            $idVehiculo     = (int)($_POST['id_vehiculo'] ?? 0);
            $idUsuarioCond  = !empty($_POST['id_usuario_conductor']) ? (int)$_POST['id_usuario_conductor'] : null;
            $idMotivo       = (int)($_POST['id_motivo'] ?? 0);
            $especificacion = trim($_POST['especificacion_motivo'] ?? '');
            $destino        = trim($_POST['destino'] ?? '');
            $kmInicial      = (int)($_POST['km_inicial'] ?? 0);
            $observaciones  = trim($_POST['observaciones'] ?? '');

            $urlDestino = $_SERVER['HTTP_REFERER'] ?? 'index.php?action=caseta';

            try {
                $db = Database::getConnection();
                $modelo = new MovimientoModel($db);

                // 1. Si se despacha desde solicitud autorizada, auto-vincular sus datos validados
                if ($idSolicitud !== null && $idSolicitud > 0) {
                    $solicitudModel = new SolicitudModel($db);
                    $solicitud = $solicitudModel->obtenerPorId($idSolicitud);

                    if (!$solicitud) {
                        throw new Exception("La solicitud #{$idSolicitud} no existe.");
                    }
                    if (($solicitud['estado_solicitud'] ?? '') !== 'Autorizada') {
                        throw new Exception("La solicitud #{$idSolicitud} no se encuentra en estado 'Autorizada' (estado actual: {$solicitud['estado_solicitud']}).");
                    }
                    if (empty($solicitud['vehiculo_deseado'])) {
                        throw new Exception("La solicitud #{$idSolicitud} no cuenta con un vehículo asignado por Administración.");
                    }

                    $idVehiculo     = (int)$solicitud['vehiculo_deseado'];
                    $idUsuarioCond  = (int)$solicitud['id_usuario_solicitante'];
                    $idMotivo       = (int)$solicitud['id_motivo'];
                    $especificacion = $solicitud['especificacion_motivo'] ?? '';
                    $destino        = $solicitud['destino'] ?? '';
                }

                if ($idVehiculo <= 0) {
                    throw new Exception('Es necesario seleccionar el vehículo para registrar la salida.');
                }
                if ($idMotivo <= 0 || empty($destino)) {
                    throw new Exception('El motivo y destino de la comisión son requeridos para autorizar la salida.');
                }

                // 2. Ejecutar registro atómico
                $idMovimiento = $modelo->registrarSalidaTransaccional([
                    'id_solicitud'          => $idSolicitud,
                    'id_vehiculo'           => $idVehiculo,
                    'id_usuario_conductor'  => $idUsuarioCond,
                    'id_motivo'             => $idMotivo,
                    'especificacion_motivo' => $especificacion ?: null,
                    'destino'               => $destino,
                    'km_inicial'            => $kmInicial,
                    'observaciones'         => $observaciones ?: null,
                    'id_usuario_caseta'     => (int)AuthMiddleware::idUsuario()
                ]);

                if ($idMovimiento > 0) {
                    // 3. Auditoría en BitacoraModel
                    try {
                        $bitacora = new BitacoraModel($db);
                        $vehiculoModel = new VehiculoModel($db);
                        $veh = $vehiculoModel->obtenerPorId($idVehiculo);
                        $eco = $veh['numero_economico'] ?? 'S/N';
                        $placas = $veh['placas'] ?? '';

                        $descBitacora = "Despacho de salida en caseta. Movimiento #{$idMovimiento}, Eco #{$eco} ({$placas}), Km Inicial: {$kmInicial}, Destino: {$destino}.";
                        if ($idSolicitud) {
                            $descBitacora .= " Solicitud de comisión asociada: #{$idSolicitud}.";
                        }
                        $bitacora->registrar(
                            (int)AuthMiddleware::idUsuario(),
                            'movimientos',
                            $idMovimiento,
                            'SALIDA',
                            $descBitacora
                        );
                    } catch (Throwable $e) {
                        error_log('Error registrando bitácora de salida en caseta: ' . $e->getMessage());
                    }

                    $_SESSION['alerta'] = [
                        'tipo' => 'success',
                        'mensaje' => "Salida registrada exitosamente con folio #{$idMovimiento}. Vehículo despachado en ruta."
                    ];
                    header('Location: ' . $urlDestino);
                    exit();
                }

                throw new Exception('No fue posible completar el registro de salida.');
            } catch (Exception $e) {
                $_SESSION['alerta'] = [
                    'tipo' => 'danger',
                    'mensaje' => $e->getMessage()
                ];
                header('Location: ' . $urlDestino);
                exit();
            }
        }

        header('Location: index.php?action=caseta');
        exit();
    }

    /**
     * Registra el retorno / entrada de una unidad en caseta con transacción SQL atómica.
     * Cierra el movimiento, actualiza odómetro y retorna el vehículo a estatus 'Disponible'.
     */
    public function entrada(): void
    {
        AuthMiddleware::verificarSesion();
        RoleMiddleware::requerirRol([
            RoleMiddleware::ROL_ADMINISTRADOR,
            RoleMiddleware::ROL_ENCARGADO_VEHICULAR
        ]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idMovimiento  = (int)($_POST['id_movimiento'] ?? 0);
            $kmFinal       = (int)($_POST['km_final'] ?? 0);
            $observaciones = trim($_POST['observaciones'] ?? '');

            $urlDestino = $_SERVER['HTTP_REFERER'] ?? 'index.php?action=caseta';

            if ($idMovimiento <= 0 || $kmFinal <= 0) {
                $_SESSION['alerta'] = [
                    'tipo' => 'danger',
                    'mensaje' => 'El folio de movimiento y el kilometraje de retorno son obligatorios.'
                ];
                header('Location: ' . $urlDestino);
                exit();
            }

            try {
                $db = Database::getConnection();
                $modelo = new MovimientoModel($db);
                $movimiento = $modelo->obtenerPorId($idMovimiento);

                if (!$movimiento) {
                    throw new Exception("El registro de movimiento #{$idMovimiento} no fue encontrado.");
                }

                $kmInicial = (int)($movimiento['km_inicial'] ?? 0);
                if ($kmFinal < $kmInicial) {
                    throw new Exception("El kilometraje final ({$kmFinal} km) no puede ser inferior al kilometraje inicial registrado ({$kmInicial} km).");
                }

                $ok = $modelo->registrarEntradaTransaccional($idMovimiento, $kmFinal, $observaciones ?: null);

                if ($ok) {
                    $kmRecorridos = $kmFinal - $kmInicial;
                    try {
                        $bitacora = new BitacoraModel($db);
                        $descBitacora = "Retorno y entrada a caseta. Movimiento #{$idMovimiento}, Km Final: {$kmFinal} ({$kmRecorridos} km recorridos). Unidad en resguardo disponible.";
                        if (!empty($movimiento['id_solicitud'])) {
                            $descBitacora .= " Solicitud de comisión #{$movimiento['id_solicitud']} marcada como Concluida.";
                        }
                        if (!empty($observaciones)) {
                            $descBitacora .= " Observaciones: {$observaciones}";
                        }
                        $bitacora->registrar(
                            (int)AuthMiddleware::idUsuario(),
                            'movimientos',
                            $idMovimiento,
                            'ENTRADA',
                            $descBitacora
                        );
                    } catch (Throwable $e) {
                        error_log('Error registrando bitácora de entrada en caseta: ' . $e->getMessage());
                    }

                    $_SESSION['alerta'] = [
                        'tipo' => 'success',
                        'mensaje' => "Retorno registrado exitosamente ({$kmRecorridos} km recorridos). Movimiento #{$idMovimiento} concluido y vehículo disponible."
                    ];
                    header('Location: ' . $urlDestino);
                    exit();
                }

                throw new Exception('No fue posible registrar la entrada del vehículo.');
            } catch (Exception $e) {
                $_SESSION['alerta'] = [
                    'tipo' => 'danger',
                    'mensaje' => $e->getMessage()
                ];
                header('Location: ' . $urlDestino);
                exit();
            }
        }

        header('Location: index.php?action=caseta');
        exit();
    }

    public function registrarSalida(): void
    {
        $this->salida();
    }

    public function registrarEntrada(): void
    {
        $this->entrada();
    }

    public function store(): void
    {
        $this->salida();
    }

    public function cerrar(): void
    {
        $this->entrada();
    }

    public function despachar(): void
    {
        $this->salida();
    }

    public function retorno(): void
    {
        $this->entrada();
    }

    public function exportarExcel(): void
    {
        AuthMiddleware::verificarSesion();
        RoleMiddleware::requerirRol([
            RoleMiddleware::ROL_ADMINISTRADOR,
            RoleMiddleware::ROL_ENCARGADO_VEHICULAR
        ]);

        try {
            $db = Database::getConnection();
            $modelo = new MovimientoModel($db);
            $movimientos = $modelo->obtenerTodosParaReporte();

            try {
                $bitacora = new BitacoraModel($db);
                $bitacora->registrar(
                    (int)AuthMiddleware::idUsuario(),
                    'movimientos',
                    0,
                    'EXPORTAR',
                    'Exportación de bitácora de movimientos a Excel (.xls)'
                );
            } catch (Throwable $e) {
                error_log('Error bitácora exportarExcel: ' . $e->getMessage());
            }

            $nombreArchivo = 'bitacora_movimientos_' . date('Ymd_His') . '.xls';

            // SpreadsheetML: Excel lo abre nativamente con doble clic sin asistente de importación
            header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
            header('Pragma: no-cache');
            header('Expires: 0');

            // Función para escapar valores en XML
            $x = function($v): string {
                return htmlspecialchars((string)$v, ENT_XML1 | ENT_QUOTES, 'UTF-8');
            };

            // Función para limpiar saltos de línea y tabulaciones internas
            $limpiar = function($texto) use ($x): string {
                if ($texto === null || $texto === '') return '';
                return $x(trim(preg_replace('/[\r\n\t]+/', ' ', (string)$texto)));
            };

            // Columnas: [ancho en pts, nombre encabezado]
            $columnas = [
                [40,  'Folio'],
                [160, 'Vehículo'],
                [90,  'Placas'],
                [140, 'Conductor'],
                [160, 'Destino'],
                [260, 'Itinerario / Paradas Intermedias'],
                [220, 'Motivo'],
                [120, 'Fecha/Hora Salida'],
                [70,  'Km Inicial'],
                [120, 'Fecha/Hora Entrada'],
                [70,  'Km Final'],
                [90,  'Km Recorridos'],
                [80,  'Estado'],
            ];

            // ── SpreadsheetML (Excel XML 2003) ──────────────────────────────────────
            echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
            echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
            echo '  xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
            echo '  xmlns:x="urn:schemas-microsoft-com:office:excel">' . "\n";

            // Estilos
            echo '<Styles>' . "\n";
            // Estilo encabezado: negrita, fondo gris claro, bordes
            echo '<Style ss:ID="sHeader">';
            echo   '<Font ss:Bold="1" ss:Size="10"/>';
            echo   '<Interior ss:Color="#F1F5F9" ss:Pattern="Solid"/>';
            echo   '<Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>';
            echo   '<Borders>';
            echo     '<Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>';
            echo   '</Borders>';
            echo '</Style>' . "\n";
            // Estilo celda texto general
            echo '<Style ss:ID="sText">';
            echo   '<Font ss:Size="10"/>';
            echo   '<Alignment ss:Vertical="Center" ss:WrapText="0"/>';
            echo   '<NumberFormat ss:Format="@"/>';
            echo '</Style>' . "\n";
            // Estilo celda numérica
            echo '<Style ss:ID="sNum">';
            echo   '<Font ss:Size="10"/>';
            echo   '<Alignment ss:Horizontal="Center" ss:Vertical="Center"/>';
            echo '</Style>' . "\n";
            echo '</Styles>' . "\n";

            // Hoja de cálculo
            echo '<Worksheet ss:Name="Bitacora Movimientos">' . "\n";
            echo '<Table>' . "\n";

            // Anchos de columna
            foreach ($columnas as [$ancho, $_t]) {
                echo '<Column ss:AutoFitWidth="0" ss:Width="' . $ancho . '"/>' . "\n";
            }

            // Fila de encabezados
            echo '<Row ss:Height="22">' . "\n";
            foreach ($columnas as [$_w, $titulo]) {
                echo '<Cell ss:StyleID="sHeader"><Data ss:Type="String">' . $x($titulo) . '</Data></Cell>' . "\n";
            }
            echo '</Row>' . "\n";

            // Filas de datos
            foreach ($movimientos as $m) {
                $folio        = (int)($m['id_movimiento'] ?? 0);
                $vehiculo     = $limpiar(($m['vehiculo_marca'] ?? '') . ' ' . ($m['vehiculo_modelo'] ?? '') . ' (Eco: ' . ($m['vehiculo_economico'] ?? 'S/N') . ')');
                $placas       = $limpiar($m['vehiculo_placas'] ?? '');
                $conductor    = $limpiar($m['conductor_nombre'] ?? 'Sin asignar');
                $destino      = $limpiar($m['destino'] ?? '');

                $itinerarioRaw = $m['itinerario_paradas'] ?? '';
                $paradasArr = !empty($itinerarioRaw) ? json_decode($itinerarioRaw, true) : null;
                $itinerarioFormateado = '';
                if (is_array($paradasArr) && !empty($paradasArr)) {
                    $escalas = [];
                    foreach ($paradasArr as $idx => $p) {
                        $num = $idx + 1;
                        $ubi = trim($p['ubicacion'] ?? '');
                        $mot = trim($p['motivo'] ?? '');
                        if ($ubi !== '') {
                            $escalas[] = $mot !== '' ? "{$num}. {$ubi} ({$mot})" : "{$num}. {$ubi}";
                        }
                    }
                    $itinerarioFormateado = implode(' | ', $escalas);
                }
                $itinerario   = $limpiar($itinerarioFormateado ?: 'Ruta directa (Sin escalas)');

                $motivoRaw    = !empty($m['especificacion_motivo'])
                    ? ($m['motivo_descripcion'] ?? 'Otro') . ' - ' . $m['especificacion_motivo']
                    : ($m['motivo_descripcion'] ?? 'General');
                $motivo       = $limpiar($motivoRaw);
                $fechaSalida  = !empty($m['fecha_hora_salida'])  ? date('d/m/Y H:i', strtotime($m['fecha_hora_salida']))  : '';
                $kmInicial    = (int)($m['km_inicial'] ?? 0);
                $fechaEntrada = !empty($m['fecha_hora_entrada']) ? date('d/m/Y H:i', strtotime($m['fecha_hora_entrada'])) : 'En tránsito';
                $kmFinal      = $m['km_final']      !== null ? (int)$m['km_final']      : '';
                $kmRec        = $m['km_recorridos'] !== null ? (int)$m['km_recorridos'] : '';
                $estado       = $limpiar($m['estado_movimiento'] ?? 'Abierto');

                echo '<Row>' . "\n";
                echo '<Cell ss:StyleID="sNum"><Data ss:Type="Number">' . $folio . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="sText"><Data ss:Type="String">' . $vehiculo . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="sText"><Data ss:Type="String">' . $placas . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="sText"><Data ss:Type="String">' . $conductor . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="sText"><Data ss:Type="String">' . $destino . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="sText"><Data ss:Type="String">' . $itinerario . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="sText"><Data ss:Type="String">' . $motivo . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="sText"><Data ss:Type="String">' . $x($fechaSalida) . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="sNum"><Data ss:Type="' . ($kmInicial !== '' ? 'Number' : 'String') . '">' . $x((string)$kmInicial) . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="sText"><Data ss:Type="String">' . $x($fechaEntrada) . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="sNum"><Data ss:Type="' . ($kmFinal !== '' ? 'Number' : 'String') . '">' . $x((string)$kmFinal) . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="sNum"><Data ss:Type="' . ($kmRec !== '' ? 'Number' : 'String') . '">' . $x((string)$kmRec) . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="sText"><Data ss:Type="String">' . $estado . '</Data></Cell>' . "\n";
                echo '</Row>' . "\n";
            }

            echo '</Table>' . "\n";
            echo '<WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">';
            echo   '<Selected/>';
            echo   '<FreezePanes/>';
            echo   '<FrozenNoSplit/>';
            echo   '<SplitHorizontal>1</SplitHorizontal>';
            echo   '<TopRowBottomPane>1</TopRowBottomPane>';
            echo   '<ActivePane>2</ActivePane>';
            echo '</WorksheetOptions>' . "\n";
            echo '</Worksheet>' . "\n";
            echo '</Workbook>' . "\n";
            exit();
        } catch (Throwable $e) {
            error_log('Error en exportarExcel movimientos: ' . $e->getMessage());
            $_SESSION['alerta'] = [
                'tipo' => 'danger',
                'mensaje' => 'Ocurrió un error al exportar la bitácora de movimientos.'
            ];
            header('Location: index.php?action=caseta');
            exit();
        }
    }

    public function exportarPdf(): void
    {
        AuthMiddleware::verificarSesion();
        RoleMiddleware::requerirRol([
            RoleMiddleware::ROL_ADMINISTRADOR,
            RoleMiddleware::ROL_ENCARGADO_VEHICULAR
        ]);

        try {
            $db = Database::getConnection();
            $modelo = new MovimientoModel($db);
            $movimientos = $modelo->obtenerTodosParaReporte();

            $totalMovimientos = count($movimientos);
            $totalAbiertos = 0;
            $totalCerrados = 0;
            $totalKmRecorridos = 0;
            foreach ($movimientos as $m) {
                if (($m['estado_movimiento'] ?? '') === 'Abierto') {
                    $totalAbiertos++;
                } else {
                    $totalCerrados++;
                }
                if (!empty($m['km_recorridos'])) {
                    $totalKmRecorridos += (int)$m['km_recorridos'];
                }
            }

            try {
                $bitacora = new BitacoraModel($db);
                $bitacora->registrar(
                    (int)AuthMiddleware::idUsuario(),
                    'movimientos',
                    0,
                    'EXPORTAR',
                    'Generación de reporte PDF / impresión de bitácora de movimientos'
                );
            } catch (Throwable $e) {
                error_log('Error bitácora exportarPdf: ' . $e->getMessage());
            }

            $tituloDocumento = 'Bitácora Oficial de Movimientos y Salidas de Caseta';

            // 1. Procesamiento de Plantilla: captura de salida HTML en búfer de memoria
            ob_start();
            require __DIR__ . '/../views/movimientos/imprimir.php';
            $html = ob_get_clean();

            // 2. Compilación nativa con Dompdf (orientación horizontal / landscape)
            require_once __DIR__ . '/../../vendor/autoload.php';

            $opciones = new \Dompdf\Options();
            $opciones->set('isHtml5ParserEnabled', true);
            $opciones->set('isRemoteEnabled', true);
            $opciones->set('defaultFont', 'Helvetica');

            $dompdf = new \Dompdf\Dompdf($opciones);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('letter', 'landscape');
            $dompdf->render();

            // 3. Cabeceras HTTP de Salida: limpiar búferes activos y enviar stream binario
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            $nombreArchivo = 'Bitacora_Movimientos_SECOTED_' . date('Ymd_His') . '.pdf';

            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . $nombreArchivo . '"');
            header('Cache-Control: private, max-age=0, must-revalidate, no-store, no-cache');
            header('Pragma: no-cache');
            header('Expires: 0');

            echo $dompdf->output();
            exit();
        } catch (Throwable $e) {
            error_log('Error en exportarPdf movimientos: ' . $e->getMessage());
            $_SESSION['alerta'] = [
                'tipo' => 'danger',
                'mensaje' => 'Ocurrió un error al generar el documento PDF.'
            ];
            header('Location: index.php?action=caseta');
            exit();
        }
    }
}
