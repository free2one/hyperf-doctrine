<?php

declare(strict_types=1);

namespace Hyperf\Doctrine;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
            ],
            'commands' => [
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for Doctrine-ORM',
                    'source' => __DIR__ . '/../publish/doctrine-orm.php',
                    'destination' => BASE_PATH . '/config/autoload/doctrine-orm.php',
                ],
            ],
        ];
    }
}
