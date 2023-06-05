<?php

declare(strict_types=1);

namespace Hyperf\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager as DoctrineEntityManager;
use Doctrine\ORM\ORMSetup;
use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Doctrine\EventManager\EventManager;
use Hyperf\Doctrine\ORM\EntityManager;
use Hyperf\Doctrine\ORM\EntityRepository;
use Hyperf\Doctrine\ORM\Repository\CoRepositoryFactory;
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
        $config = ApplicationContext::getContainer()->get(ConfigInterface::class);
        $systemConfig = $config->get('doctrine-orm');
        if (! isset($systemConfig[$poolName])) {
            throw new RuntimeException('Hyperf-Doctrine-ORM cannot find the configuration');
        }

        $configurationConfig = $systemConfig[$poolName]['configuration'];
        $connectionConfig = $systemConfig[$poolName]['connection'];
        $cache = (isset($configurationConfig['cache']) && $configurationConfig['cache'])
            ? ApplicationContext::getContainer()->make($configurationConfig['cache']['class'], $configurationConfig['cache']['constructor']) : null;
        $configuration = ORMSetup::createAttributeMetadataConfiguration(
            paths: $configurationConfig['paths'],
            isDevMode: $configurationConfig['isDevMode'],
            proxyDir: $configurationConfig['proxyDir'],
            cache: $cache
        );
        $configuration->setRepositoryFactory(new CoRepositoryFactory());
        $configuration->setDefaultRepositoryClassName(EntityRepository::class);
        if (isset($configurationConfig['metadataCache']) && $configurationConfig['metadataCache']) {
            $configuration->setMetadataCache(ApplicationContext::getContainer()->make($configurationConfig['metadataCache']['class'], $configurationConfig['metadataCache']['constructor']));
        }
        if (isset($configurationConfig['queryCache']) && $configurationConfig['queryCache']) {
            $configuration->setQueryCache(ApplicationContext::getContainer()->make($configurationConfig['queryCache']['class'], $configurationConfig['queryCache']['constructor']));
        }
        if (isset($configurationConfig['resultCache']) && $configurationConfig['resultCache']) {
            $configuration->setResultCache(ApplicationContext::getContainer()->make($configurationConfig['resultCache']['class'], $configurationConfig['resultCache']['constructor']));
        }

        $connection = DriverManager::getConnection(params: $connectionConfig, config: $configuration);
        $em = static::createManager($connection, $configuration);
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
