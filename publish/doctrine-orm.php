<?php

declare(strict_types=1);
use Hyperf\Doctrine\Cache\CacheItemPool;
use Hyperf\Doctrine\DBAL\Driver\PDO\MySQL\HyperfDatabaseDriver;
use Hyperf\Doctrine\DBAL\HyperfDatabaseConnection;

return [
    'default' => [
        'configuration' => [
            'paths' => [BASE_PATH . '/app'],
            'isDevMode' => false,
            'proxyDir' => BASE_PATH . '/runtime/doctrine-orm',
            'cache' => [
                'class' => CacheItemPool::class,
                'constructor' => [
                    'config' => [
                        'driverName' => 'default',
                        'ttl' => 60 * 60 * 24,
                    ],
                ],
            ],
            'metadataCache' => null,
            'queryCache' => null,
            'resultCache' => null,
            'filters' => [
            ],
            'listeners' => [
            ],
            'functions' => [
            ],
        ],
        'connection' => [
            'driverClass' => HyperfDatabaseDriver::class,
            'wrapperClass' => HyperfDatabaseConnection::class,
            'pool' => 'default',
        ],
    ],
];
