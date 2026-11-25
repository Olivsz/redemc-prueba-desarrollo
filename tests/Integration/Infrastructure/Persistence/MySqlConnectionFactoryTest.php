<?php

declare(strict_types=1);

namespace Tests\Integration\Infrastructure\Persistence;

use App\Infrastructure\Persistence\DatabaseConfig;
use App\Infrastructure\Persistence\MySqlConnectionFactory;
use PDO;
use PDOException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MySqlConnectionFactoryTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $config = DatabaseConfig::fromEnvironment(getenv());

        $this->connection = MySqlConnectionFactory::create($config);
    }

    #[Test]
    public function connectsToTheDatabase(): void
    {
        $result = $this->connection->query('SELECT 1')->fetchColumn();

        self::assertSame(1, $result);
    }

    #[Test]
    public function throwsAnExceptionWhenAQueryFails(): void
    {
        $this->expectException(PDOException::class);

        $this->connection->query('SELECT * FROM missing_table');
    }

    #[Test]
    public function returnsRowsAsAssociativeArrays(): void
    {
        $row = $this->connection->query("SELECT 'Ana' AS name")->fetch();

        self::assertSame(['name' => 'Ana'], $row);
    }

    #[Test]
    public function usesNativePreparedStatements(): void
    {
        self::assertFalse($this->connection->getAttribute(PDO::ATTR_EMULATE_PREPARES));
    }

    #[Test]
    public function usesUtcAsTheSessionTimeZone(): void
    {
        $timeZone = $this->connection->query('SELECT @@session.time_zone')->fetchColumn();

        self::assertSame('+00:00', $timeZone);
    }
}
