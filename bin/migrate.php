<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\DatabaseConfig;
use App\Infrastructure\Persistence\MySqlConnectionFactory;
use App\Infrastructure\Persistence\SchemaMigrator;

require dirname(__DIR__) . '/vendor/autoload.php';

$connection = MySqlConnectionFactory::create(DatabaseConfig::fromEnvironment(getenv()));

(new SchemaMigrator($connection))->migrate(dirname(__DIR__) . '/database/schema.sql');

echo "Database schema is up to date.\n";
