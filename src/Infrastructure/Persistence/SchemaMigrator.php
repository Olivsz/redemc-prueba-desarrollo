<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use PDO;
use RuntimeException;

final readonly class SchemaMigrator
{
    public function __construct(private PDO $connection)
    {
    }

    public function migrate(string $schemaFile): void
    {
        if (!is_readable($schemaFile)) {
            throw new RuntimeException("Schema file not found: {$schemaFile}.");
        }

        $this->connection->exec(file_get_contents($schemaFile));
    }
}
