<?php

declare(strict_types=1);

namespace Hyperf\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager as DoctrineEntityManager;
use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Hyperf\Doctrine\EventManager\EventManager;
use Hyperf\Doctrine\ORM\EntityManager;
use Hyperf\Doctrine\ORM\ORMSetup;
use Psr\EventDispatcher\EventDispatcherInterface;
use RuntimeException;

class EntityManagerFactory
{
    public static array $contextKeys = [
        'entityManager' => 'doctrine.orm.entityManager',
        'entityManagerMap' => 'doctrine.orm.entityManagerMap',
    ];

    public static function getManager($poolName = 'default'): EntityManager
    {
        /** @var EntityManager $em */
        $em = Context::get(static::getManagerKey($poolName));
        if ($em && $em->isOpen()) {
            return $em;
        }

        $poolName = $poolName ?: 'default';
        $configuration = ORMSetup::create($poolName);
        $connection = DriverManager::getConnection(params: ORMSetup::getConfig($poolName)['connection'], config: $configuration);
        $em = static::createManager($connection, $configuration);
        ORMSetup::buildFilters($poolName, $configuration, $em);
        ORMSetup::buildListeners($poolName, $em);
        ORMSetup::buildCustomFunctions($poolName, $configuration);

        Context::set(static::getManagerKey($poolName), $em);

        return $em;
    }

    public static function getManagerByWrapped(object $wrapped): EntityManager
    {
        $em = Context::get(static::getWrapKey($wrapped));
        if (empty($em)) {
            throw new RuntimeException('The associated manager could not be found');
        }

        return $em;
    }

    public static function createManager(Connection $conn, Configuration $config): EntityManager
    {
        $doctrineEntityManager = new DoctrineEntityManager(
            $conn,
            $config,
            new EventManager(ApplicationContext::getContainer()->get(EventDispatcherInterface::class))
        );
        $em = new EntityManager($doctrineEntityManager);
        Context::set(static::getWrapKey($doctrineEntityManager), $em);

        return $em;
    }

    private static function getWrapKey(object $obj): string
    {
        return static::$contextKeys['entityManagerMap'] . '.' . spl_object_id($obj);
    }

    private static function getManagerKey($poolName): string
    {
        return static::$contextKeys['entityManager'] . '.' . $poolName;
    }
}
