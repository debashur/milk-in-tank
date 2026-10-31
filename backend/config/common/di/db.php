<?php

declare(strict_types=1);

use Yiisoft\Db\Cache\SchemaCache;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Sqlite\Connection;
use Yiisoft\Db\Sqlite\Driver;

/** @var array $params */

return [
    ConnectionInterface::class => static function (SchemaCache $schemaCache) use ($params): Connection {
        $db = new Connection(
            new Driver($params['yiisoft/db-sqlite']['dsn']),
            $schemaCache,
        );
        $db->createCommand('PRAGMA foreign_keys = ON')->execute();

        return $db;
    },
];
