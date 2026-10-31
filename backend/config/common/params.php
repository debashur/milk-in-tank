<?php

declare(strict_types=1);

use Yiisoft\Db\Sqlite\Dsn;

return [
    'application' => require __DIR__ . '/application.php',

    'yiisoft/aliases' => [
        'aliases' => require __DIR__ . '/aliases.php',
    ],

    'yiisoft/db-sqlite' => [
        'dsn' => new Dsn(databaseName: dirname(__DIR__, 2) . '/runtime/database.sqlite'),
    ],

    'yiisoft/db-migration' => [
        'newMigrationNamespace' => 'App\\Migration',
        'sourceNamespaces' => ['App\\Migration'],
    ],
];
