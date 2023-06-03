<?php

declare(strict_types=1);

namespace Hyperf\Doctrine\DBAL;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Hyperf\Database\ConnectionInterface;

class HyperfDatabaseConnection extends Connection
{
    public function __construct(array $params, Driver $driver, ?Configuration $config = null, ?EventManager $eventManager = null)
    {
        parent::__construct($params, $driver, $config, $eventManager);
    }

    public function getHyperfConnection(): ConnectionInterface
    {
        $this->connect();
        return $this->_conn->getHyperfConnection();
    }
}
