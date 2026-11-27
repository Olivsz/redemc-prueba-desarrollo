<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Persistence;

use App\Infrastructure\Persistence\DatabaseConfig;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class DatabaseConfigTest extends TestCase
{
    private const array VALID_ENVIRONMENT = [
        'DB_HOST' => 'db',
        'DB_PORT' => '3306',
        'DB_NAME' => 'redemc',
        'DB_USER' => 'redemc',
        'DB_PASSWORD' => 'secret',
    ];

    #[Test]
    public function buildsTheDsnFromTheEnvironment(): void
    {
        $config = DatabaseConfig::fromEnvironment(self::VALID_ENVIRONMENT);

        self::assertSame('mysql:host=db;port=3306;dbname=redemc;charset=utf8mb4', $config->dsn());
    }

    #[Test]
    public function exposesTheCredentials(): void
    {
        $config = DatabaseConfig::fromEnvironment(self::VALID_ENVIRONMENT);

        self::assertSame('redemc', $config->user);
        self::assertSame('secret', $config->password);
    }

    #[Test]
    #[DataProvider('requiredVariables')]
    public function failsWhenARequiredVariableIsMissing(string $variable): void
    {
        $environment = self::VALID_ENVIRONMENT;
        unset($environment[$variable]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($variable);

        DatabaseConfig::fromEnvironment($environment);
    }

    #[Test]
    #[DataProvider('requiredVariables')]
    public function failsWhenARequiredVariableIsEmpty(string $variable): void
    {
        $environment = [...self::VALID_ENVIRONMENT, $variable => ''];

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($variable);

        DatabaseConfig::fromEnvironment($environment);
    }

    public static function requiredVariables(): iterable
    {
        yield 'host' => ['DB_HOST'];
        yield 'port' => ['DB_PORT'];
        yield 'database' => ['DB_NAME'];
        yield 'user' => ['DB_USER'];
        yield 'password' => ['DB_PASSWORD'];
    }

    #[Test]
    #[DataProvider('invalidPorts')]
    public function failsWhenThePortIsNotAValidPortNumber(string $port): void
    {
        $environment = [...self::VALID_ENVIRONMENT, 'DB_PORT' => $port];

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('DB_PORT');

        DatabaseConfig::fromEnvironment($environment);
    }

    public static function invalidPorts(): iterable
    {
        yield 'text' => ['abc'];
        yield 'zero' => ['0'];
        yield 'above the maximum' => ['65536'];
        yield 'decimal' => ['33.06'];
    }
}
