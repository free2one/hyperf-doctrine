<?php

namespace Hyperf\Doctrine\DBAL\Driver\PDO\MySQL;

use Doctrine\DBAL\Driver\AbstractMySQLDriver;
use Hyperf\DbConnection\Db;
use Hyperf\Doctrine\DBAL\Driver\PDO\HyperfDatabaseConnection;

class HyperfDatabaseDriver extends AbstractMySQLDriver
{
    public function connect(array $params)
    {
        return new HyperfDatabaseConnection(Db::connection($params['pool'] ?? 'default'));
    }
}