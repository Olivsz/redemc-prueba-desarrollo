<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use RuntimeException;
use SensitiveParameter;

/**
 * Datos de conexión a MySQL. Falla al arrancar si falta alguno, en lugar de
 * dejar que PDO devuelva un error confuso en el primer pedido.
 */
final readonly class DatabaseConfig
{
    private const string CHARSET = 'utf8mb4';
    private const int MIN_PORT = 1;
    private const int MAX_PORT = 65535;

    public function __construct(
        public string $host,
        public int $port,
        public string $database,
        public string $user,
        #[SensitiveParameter] public string $password,
    ) {
    }

    public static function fromEnvironment(array $environment): self
    {
        return new self(
            host: self::required($environment, 'DB_HOST'),
            port: self::port($environment),
            database: self::required($environment, 'DB_NAME'),
            user: self::required($environment, 'DB_USER'),
            password: self::required($environment, 'DB_PASSWORD'),
        );
    }

    public function dsn(): string
    {
        return sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $this->host,
            $this->port,
            $this->database,
            self::CHARSET,
        );
    }

    private static function required(array $environment, string $name): string
    {
        $value = $environment[$name] ?? '';

        if ($value === '') {
            throw new RuntimeException("Missing required environment variable {$name}.");
        }

        return $value;
    }

    private static function port(array $environment): int
    {
        $port = filter_var(
            self::required($environment, 'DB_PORT'),
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => self::MIN_PORT, 'max_range' => self::MAX_PORT]],
        );

        if ($port === false) {
            throw new RuntimeException(
                sprintf('Environment variable DB_PORT must be an integer between %d and %d.', self::MIN_PORT, self::MAX_PORT),
            );
        }

        return $port;
    }
}
