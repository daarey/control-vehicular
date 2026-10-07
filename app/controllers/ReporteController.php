<?php

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/RoleMiddleware.php';
require_once __DIR__ . '/../models/BitacoraModel.php';

/**
 * ReporteController.php
 * Controlador para generación de reportes y visualización de la bitácora de auditoría.
 */
class ReporteController
{
    public function index(): void
    {
        AuthMiddleware::verificarSesion();
        RoleMiddleware::requerirRol(RoleMiddleware::ROL_ADMINISTRADOR);

        $bitacora = [];
        try {
            $db = Database::getConnection();
            $modelo = new BitacoraModel($db);
            $bitacora = $modelo->obtenerRecientes(30);
        } catch (Throwable $e) {
            error_log('Error en ReporteController: ' . $e->getMessage());
        }

        $tituloPagina = 'Reportes y Bitácora de Auditoría';
        $paginaActual = 'reportes';

        require_once __DIR__ . '/../views/reportes/index.php';
    }

    public function exportarExcel(): void
    {
        AuthMiddleware::verificarSesion();
        RoleMiddleware::requerirRol(RoleMiddleware::ROL_ADMINISTRADOR);

        try {
            $db = Database::getConnection();
            $modelo = new BitacoraModel($db);
            $eventos = $modelo->obtenerTodosParaReporte();

            try {
                $modelo->registrar(
                    (int)AuthMiddleware::idUsuario(),
                    'bitacora',
                    0,
                    'EXPORTAR',
                    'Exportación de bitácora de auditoría a Excel (.xls)'
                );
            } catch (Throwable $e) {
                error_log('Error bitácora exportarExcel reportes: ' . $e->getMessage());
            }

            $nombreArchivo = 'bitacora_auditoria_' . date('Ymd_His') . '.xls';

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
                [120, 'Fecha y Hora'],
                [150, 'Usuario'],
                [190, 'Correo Electrónico'],
                [120, 'Módulo / Tabla'],
                [90,  'Acción'],
                [350, 'Detalles'],
            ];

            // ── SpreadsheetML (Excel XML 2003) ──────────────────────────────────────
            echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
            echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
            echo '  xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
            echo '  xmlns:x="urn:schemas-microsoft-com:office:excel">' . "\n";

            // Estilos
            echo '<Styles>' . "\n";
            echo '<Style ss:ID="sHeader">';
            echo   '<Font ss:Bold="1" ss:Size="10"/>';
            echo   '<Interior ss:Color="#F1F5F9" ss:Pattern="Solid"/>';
            echo   '<Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>';
            echo   '<Borders>';
            echo     '<Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>';
            echo   '</Borders>';
            echo '</Style>' . "\n";
            echo '<Style ss:ID="sText">';
            echo   '<Font ss:Size="10"/>';
            echo   '<Alignment ss:Vertical="Center" ss:WrapText="0"/>';
            echo   '<NumberFormat ss:Format="@"/>';
            echo '</Style>' . "\n";
            echo '</Styles>' . "\n";

            // Hoja de cálculo
            echo '<Worksheet ss:Name="Bitacora Auditoria">' . "\n";
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
            foreach ($eventos as $e) {
                $fecha   = !empty($e['fecha'])           ? date('d/m/Y H:i', strtotime($e['fecha'])) : '';
                $usuario = $limpiar($e['usuario_nombre'] ?? 'Usuario');
                $correo  = $limpiar($e['usuario_correo'] ?? 'Sin correo');
                $tabla   = $limpiar($e['tabla_afectada'] ?? '');
                $accion  = $limpiar($e['accion']         ?? '');
                $detalle = $limpiar($e['detalle']        ?? '');

                echo '<Row>' . "\n";
                echo '<Cell ss:StyleID="sText"><Data ss:Type="String">' . $x($fecha)   . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="sText"><Data ss:Type="String">' . $usuario . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="sText"><Data ss:Type="String">' . $correo  . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="sText"><Data ss:Type="String">' . $tabla   . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="sText"><Data ss:Type="String">' . $accion  . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="sText"><Data ss:Type="String">' . $detalle . '</Data></Cell>' . "\n";
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
            error_log('Error en exportarExcel auditoria: ' . $e->getMessage());
            $_SESSION['alerta'] = [
                'tipo' => 'danger',
                'mensaje' => 'Ocurrió un error al exportar la bitácora de auditoría.'
            ];
            header('Location: index.php?action=reportes');
            exit();
        }
    }

    public function exportarPdf(): void
    {
        AuthMiddleware::verificarSesion();
        RoleMiddleware::requerirRol(RoleMiddleware::ROL_ADMINISTRADOR);

        try {
            $db = Database::getConnection();
            $modelo = new BitacoraModel($db);
            $bitacora = $modelo->obtenerTodosParaReporte();

            $totalEventos = count($bitacora);
            $totalCrear = 0;
            $totalActualizar = 0;
            $totalEliminar = 0;
            $totalSalidasEntradas = 0;

            foreach ($bitacora as $b) {
                $acc = strtoupper($b['accion'] ?? '');
                if ($acc === 'CREAR') $totalCrear++;
                elseif ($acc === 'ACTUALIZAR') $totalActualizar++;
                elseif ($acc === 'ELIMINAR' || $acc === 'BAJA') $totalEliminar++;
                elseif ($acc === 'SALIDA' || $acc === 'ENTRADA') $totalSalidasEntradas++;
            }

            try {
                $modelo->registrar(
                    (int)AuthMiddleware::idUsuario(),
                    'bitacora',
                    0,
                    'EXPORTAR',
                    'Generación de reporte PDF / impresión de bitácora de auditoría'
                );
            } catch (Throwable $e) {
                error_log('Error bitácora exportarPdf reportes: ' . $e->getMessage());
            }

            $tituloDocumento = 'Bitácora Oficial de Auditoría y Trazabilidad del Sistema';

            // 1. Procesamiento de Plantilla: captura de salida HTML en búfer de memoria
            ob_start();
            require __DIR__ . '/../views/reportes/imprimir.php';
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

            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="Reporte_SECOTED.pdf"');
            header('Cache-Control: private, max-age=0, must-revalidate, no-store, no-cache');
            header('Pragma: no-cache');
            header('Expires: 0');

            echo $dompdf->output();
            exit();
        } catch (Throwable $e) {
            error_log('Error en exportarPdf auditoria: ' . $e->getMessage());
            $_SESSION['alerta'] = [
                'tipo' => 'danger',
                'mensaje' => 'Ocurrió un error al generar el documento PDF.'
            ];
            header('Location: index.php?action=reportes');
            exit();
        }
    }
}
