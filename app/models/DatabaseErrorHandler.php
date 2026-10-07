<?php

/**
 * DatabaseErrorHandler.php
 * Manejador centralizado de excepciones PDO para la capa de Modelos.
 * Traduce errores de integridad (1062, 1451, 1452, SQLSTATE 23000) a excepciones
 * amigables en español comprensibles por los usuarios del sistema.
 */
class DatabaseErrorHandler
{
    /**
     * Intercepta la PDOException y lanza una Exception con mensaje en español.
     *
     * @param PDOException $e
     * @param string $accion Nombre de la acción para el mensaje por defecto
     * @throws Exception
     * @return never
     */
    public static function handle(PDOException $e, string $accion = 'guardar el registro'): never
    {
        $errorCode = (int)($e->errorInfo[1] ?? 0);
        $sqlState  = (string)($e->errorInfo[0] ?? $e->getCode());
        $message   = $e->getMessage();
        $msgLower  = strtolower($message);

        // 1. Detección de claves duplicadas (MySQL 1062 / SQLSTATE 23000)
        if ($errorCode === 1062 || ($sqlState === '23000' && str_contains($msgLower, 'duplicate entry'))) {
            if (str_contains($msgLower, 'placas')) {
                throw new Exception("Las placas ingresadas ya se encuentran registradas en el parque vehicular.");
            }
            if (str_contains($msgLower, 'numero_economico') || str_contains($msgLower, 'economico')) {
                throw new Exception("El número económico ya está asignado a otro vehículo.");
            }
            if (str_contains($msgLower, 'correo') || str_contains($msgLower, 'email')) {
                throw new Exception("El correo electrónico ya pertenece a una cuenta registrada.");
            }
            if (str_contains($msgLower, 'numero_empleado')) {
                throw new Exception("El número de empleado ya pertenece a un conductor registrado.");
            }
            if (str_contains($msgLower, 'nombre_area')) {
                throw new Exception("El nombre del área ya se encuentra registrado en el sistema.");
            }
            throw new Exception("El registro contiene información duplicada que ya existe en el sistema.");
        }

        // 2. Detección de violaciones de integridad referencial / llaves foráneas (MySQL 1451, 1452 / SQLSTATE 23000)
        if ($errorCode === 1451 || $errorCode === 1452 || str_contains($msgLower, 'foreign key constraint fails')) {
            throw new Exception("No se puede realizar la acción porque el registro está vinculado a otros movimientos en el sistema.");
        }

        // 3. Otros errores no controlados de base de datos
        error_log("DatabaseErrorHandler [SQLSTATE {$sqlState}, Code {$errorCode}]: " . $message);
        throw new Exception("Ocurrió un problema con la base de datos al {$accion}. Por favor intente más tarde.");
    }
}
