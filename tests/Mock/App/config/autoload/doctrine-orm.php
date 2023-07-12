<?php

declare(strict_types=1);

use Gedmo\SoftDeleteable\SoftDeleteableListener;

return [
    'default' => [
        'configuration' => [
            'paths' => [BASE_PATH . '/app'],
            'isDevMode' => false,
            'proxyDir' => BASE_PATH . '/runtime/doctrine-orm',
            'cache' => [
                'class' => Hyperf\Doctrine\Cache\CacheItemPool::class,
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
                [
                    'name' => 'soft-deleteable',
                    'className' => Gedmo\SoftDeleteable\Filter\SoftDeleteableFilter::class,
                    'enable' => true,
                ],
            ],
            'listeners' => [
                SoftDeleteableListener::class,
            ],
        ],
        'connection' => [
            'driverClass' => Hyperf\Doctrine\DBAL\Driver\PDO\MySQL\HyperfDatabaseDriver::class,
            'wrapperClass' => Hyperf\Doctrine\DBAL\HyperfDatabaseConnection::class,
            'pool' => 'default',
        ],
    ],
];
