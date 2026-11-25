<?php

declare(strict_types=1);

namespace Tests\Integration\Infrastructure\Persistence;

use App\Infrastructure\Persistence\DatabaseConfig;
use App\Infrastructure\Persistence\MySqlConnectionFactory;
use App\Infrastructure\Persistence\SchemaMigrator;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class SchemaMigratorTest extends TestCase
{
    private const string PROBE_SCHEMA = __DIR__ . '/Fixtures/probe_schema.sql';
    private const string PROBE_TABLE = 'migration_probe';
    private const string PROJECT_SCHEMA = __DIR__ . '/../../../../database/schema.sql';

    private PDO $connection;
    private SchemaMigrator $migrator;

    protected function setUp(): void
    {
        $this->connection = MySqlConnectionFactory::create(DatabaseConfig::fromEnvironment(getenv()));
        $this->migrator = new SchemaMigrator($this->connection);
    }

    protected function tearDown(): void
    {
        $this->connection->exec('DROP TABLE IF EXISTS ' . self::PROBE_TABLE);
    }

    #[Test]
    public function createsTheTablesDefinedInTheSchemaFile(): void
    {
        $this->migrator->migrate(self::PROBE_SCHEMA);

        self::assertTrue($this->tableExists(self::PROBE_TABLE));
    }

    #[Test]
    public function appliesTheProjectSchemaMoreThanOnceWithoutErrors(): void
    {
        $this->migrator->migrate(self::PROJECT_SCHEMA);
        $this->migrator->migrate(self::PROJECT_SCHEMA);

        self::assertTrue($this->tableExists('contacts'));
    }

    #[Test]
    public function failsWhenTheSchemaFileDoesNotExist(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('missing.sql');

        $this->migrator->migrate(__DIR__ . '/Fixtures/missing.sql');
    }

    private function tableExists(string $table): bool
    {
        $statement = $this->connection->prepare(
            'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?',
        );
        $statement->execute([$table]);

        return $statement->fetchColumn() === 1;
    }
}
