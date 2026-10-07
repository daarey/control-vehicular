<?php

// Asegurar zona horaria en el bootstrap de la base de datos
date_default_timezone_set('America/Mexico_City');

/**
 * Database.php
 * Capa de configuración: gestiona la conexión PDO a la base de datos.
 */
class Database
{
    private static ?PDO $instancia = null;

    // Constructor privado: impide instanciación directa
    private function __construct() {}

    /**
     * Retorna la instancia única de PDO (patrón Singleton).
     */
    public static function getConnection(): PDO
    {
        if (self::$instancia === null) {
            $host     = 'localhost';
            $dbname   = 'control_vehicular';
            $username = 'root';
            $password = '';
            $charset  = 'utf8mb4';

            $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";

            $opciones = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset}, time_zone = '-06:00'",
            ];

            try {
                self::$instancia = new PDO($dsn, $username, $password, $opciones);
            } catch (PDOException $e) {
                // En producción, registrar el error en un log; nunca exponer detalles
                error_log('Error de conexión BD: ' . $e->getMessage());
                die('No se pudo establecer la conexión con la base de datos.');
            }
        }

        return self::$instancia;
    }
}
