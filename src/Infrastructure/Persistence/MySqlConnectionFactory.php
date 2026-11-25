<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use PDO;
use Pdo\Mysql;

final class MySqlConnectionFactory
{
    private const array OPTIONS = [
        // Un error de SQL lanza una excepción en lugar de devolver false sin avisar.
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        // Cada fila llega como array con los nombres de las columnas: ['name' => 'Ana'].
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // Consultas preparadas reales: MySQL recibe el SQL y los datos por separado,
        // así un dato nunca puede ejecutarse como SQL (inyección SQL).
        PDO::ATTR_EMULATE_PREPARES => false,
        // Las fechas que genera MySQL, como created_at, quedan en UTC.
        Mysql::ATTR_INIT_COMMAND => "SET time_zone = '+00:00'",
    ];

    public static function create(DatabaseConfig $config): PDO
    {
        return new PDO($config->dsn(), $config->user, $config->password, self::OPTIONS);
    }
}
