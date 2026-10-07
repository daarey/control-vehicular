<?php

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

/**
 * ArchivoController.php
 * Controlador seguro de descarga/visualización de archivos adjuntos.
 *
 * Verifica sesión activa, sanitiza rutas con basename() para prevenir
 * Directory Traversal (../), y sirve el contenido con cabeceras apropiadas.
 */
class ArchivoController
{
    /**
     * Directorio raíz de almacenamiento de archivos.
     * FUERA del Document Root de Apache.
     */
    private const STORAGE_BASE = __DIR__ . '/../../storage/uploads';

    /**
     * Extensiones permitidas para servir.
     */
    private const EXTENSIONES_PERMITIDAS = ['jpg', 'jpeg', 'png', 'webp', 'pdf', 'gif'];

    /**
     * Genera la URL segura para un archivo dado su path o identificador.
     */
    public static function url(?string $path): string
    {
        if (empty($path)) {
            return '';
        }
        $filename = basename(str_replace('\\', '/', $path));
        $tipo = 'licencias';
        if (strpos($path, 'licencias') !== false) {
            $tipo = 'licencias';
        }
        return 'index.php?action=ver_archivo&tipo=' . urlencode($tipo) . '&archivo=' . urlencode($filename);
    }

    /**
     * Sirve un archivo protegido previa autenticación.
     *
     * Parámetros aceptados vía GET:
     *   - tipo:    Subdirectorio (ej. "licencias")
     *   - archivo: Nombre del archivo o ruta (ej. "lic_abc123.jpg", "uploads/licencias/lic_abc123.jpg", "storage:licencias/lic_abc123.jpg")
     */
    public function servir(): void
    {
        AuthMiddleware::verificarSesion();

        $archivoRaw = trim($_GET['archivo'] ?? '');
        $tipoRaw    = trim($_GET['tipo'] ?? '');

        if (empty($archivoRaw) && empty($tipoRaw)) {
            http_response_code(400);
            echo 'Parámetros insuficientes para localizar el archivo.';
            exit();
        }

        // Si tipo está vacío pero archivo contiene directorio
        if (empty($tipoRaw)) {
            if (strpos($archivoRaw, 'licencias') !== false) {
                $tipoRaw = 'licencias';
            } else {
                $tipoRaw = 'licencias';
            }
        }

        // Sanitización estricta: prevenir Directory Traversal usando basename
        $tipoSanitizado    = basename(str_replace('\\', '/', $tipoRaw));
        $archivoSanitizado = basename(str_replace('\\', '/', $archivoRaw));

        // Validar extensión
        $extension = strtolower(pathinfo($archivoSanitizado, PATHINFO_EXTENSION));
        if (!in_array($extension, self::EXTENSIONES_PERMITIDAS, true)) {
            http_response_code(403);
            echo 'Tipo de archivo no permitido.';
            exit();
        }

        // Construir ruta absoluta y verificar existencia
        $rutaCompleta = realpath(self::STORAGE_BASE . '/' . $tipoSanitizado . '/' . $archivoSanitizado);

        // Verificar que la ruta resuelta está dentro del directorio permitido
        $basePath = realpath(self::STORAGE_BASE);
        if ($rutaCompleta === false || $basePath === false || !str_starts_with($rutaCompleta, $basePath)) {
            http_response_code(404);
            echo 'Archivo no encontrado.';
            exit();
        }

        if (!is_file($rutaCompleta) || !is_readable($rutaCompleta)) {
            http_response_code(404);
            echo 'Archivo no encontrado o no accesible.';
            exit();
        }

        // Detectar tipo MIME real del archivo
        $mimeType = mime_content_type($rutaCompleta) ?: 'application/octet-stream';

        // Cabeceras de respuesta
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($rutaCompleta));
        header('Content-Disposition: inline; filename="' . $archivoSanitizado . '"');
        header('Cache-Control: private, max-age=3600');
        header('X-Content-Type-Options: nosniff');

        // Servir contenido
        readfile($rutaCompleta);
        exit();
    }
}
