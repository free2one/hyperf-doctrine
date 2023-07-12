<?php

declare(strict_types=1);

namespace Hyperf\Doctrine\ORM;

use Doctrine\ORM\Configuration;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Doctrine\ORM\Repository\CoRepositoryFactory;
use RuntimeException;

class ORMSetup
{
    public static function getConfig(string $poolName): array
    {
        $config = ApplicationContext::getContainer()->get(ConfigInterface::class);
        $systemConfig = $config->get('doctrine-orm');

        if (! isset($systemConfig[$poolName])) {
            throw new RuntimeException('Hyperf-Doctrine-ORM cannot find the configuration');
        }

        return $systemConfig[$poolName];
    }

    public static function create(string $poolName): Configuration
    {
        $configurationConfig = self::getConfig($poolName)['configuration'];
        $cache = (isset($configurationConfig['cache']) && $configurationConfig['cache']) ? ApplicationContext::getContainer()->make($configurationConfig['cache']['class'], $configurationConfig['cache']['constructor']) : null;

        $configuration = \Doctrine\ORM\ORMSetup::createAttributeMetadataConfiguration(
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

        return $configuration;
    }

    public static function buildFilters(string $poolName, Configuration $configuration, EntityManager $em): void
    {
        $config = self::getConfig($poolName)['configuration'];

        if (! isset($config['filters']) || ! is_array($config['filters'])) {
            return;
        }

        foreach ($config['filters'] as $filter) {
            if (! isset($filter['name']) || ! isset($filter['className'])) {
                continue;
            }

            $configuration->addFilter($filter['name'], $filter['className']);
            isset($filter['enable']) && $filter['enable'] == true && $em->getFilters()->enable($filter['name']);
        }
    }

    public static function buildListeners(string $poolName, EntityManager $em): void
    {
        $config = self::getConfig($poolName)['configuration'];

        if (! isset($config['listeners']) || ! is_array($config['listeners'])) {
            return;
        }

        foreach ($config['listeners'] as $listener) {
            $listenerObj = is_callable($listener) ? $listener($em) : ApplicationContext::getContainer()->make($listener);
            $em->getEventManager()->addEventSubscriber($listenerObj);
        }
    }
}
