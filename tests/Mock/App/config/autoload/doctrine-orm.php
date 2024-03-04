<?php

declare(strict_types=1);

use Gedmo\SoftDeleteable\Filter\SoftDeleteableFilter;
use Gedmo\SoftDeleteable\SoftDeleteableListener;
use Hyperf\Doctrine\Cache\CacheItemPool;
use Hyperf\Doctrine\DBAL\Driver\PDO\MySQL\HyperfDatabaseDriver;
use Hyperf\Doctrine\DBAL\HyperfDatabaseConnection;
use HyperfTest\Mock\CustomFunction\NowFunction;
use HyperfTest\Mock\CustomFunction\RandFunction;
use HyperfTest\Mock\CustomFunction\SubstringFunction;

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
                [
                    'name' => 'soft-deleteable',
                    'className' => SoftDeleteableFilter::class,
                    'enable' => true,
                ],
            ],
            'listeners' => [
                SoftDeleteableListener::class,
            ],
            'functions' => [
                [
                    'name' => 'test_substring',
                    'className' => SubstringFunction::class,
                    'type' => 'string',
                ],
                [
                    'name' => 'test_rand',
                    'className' => RandFunction::class,
                    'type' => 'numeric',
                ],
                [
                    'name' => 'test_now',
                    'className' => NowFunction::class,
                    'type' => 'datetime',
                ],
            ],
        ],
        'connection' => [
            'driverClass' => HyperfDatabaseDriver::class,
            'wrapperClass' => HyperfDatabaseConnection::class,
            'pool' => 'default',
        ],
    ],
];
