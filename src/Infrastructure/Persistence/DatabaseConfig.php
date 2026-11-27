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
            host: self::readRequired($environment, 'DB_HOST'),
            port: self::readPort($environment),
            database: self::readRequired($environment, 'DB_NAME'),
            user: self::readRequired($environment, 'DB_USER'),
            password: self::readRequired($environment, 'DB_PASSWORD'),
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

    private static function readRequired(array $environment, string $name): string
    {
        $value = $environment[$name] ?? '';

        if ($value === '') {
            throw new RuntimeException("Missing required environment variable {$name}.");
        }

        return $value;
    }

    private static function readPort(array $environment): int
    {
        $port = self::readRequired($environment, 'DB_PORT');

        if (!self::isValidPort($port)) {
            throw new RuntimeException(
                sprintf('Environment variable DB_PORT must be an integer between %d and %d.', self::MIN_PORT, self::MAX_PORT),
            );
        }

        return (int) $port;
    }

    private static function isValidPort(string $port): bool
    {
        return ctype_digit($port)
            && (int) $port >= self::MIN_PORT
            && (int) $port <= self::MAX_PORT;
    }
}
