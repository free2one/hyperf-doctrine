<?php

declare(strict_types=1);

namespace Hyperf\Doctrine\ORM\Repository;

use Doctrine\Deprecations\Deprecation;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Repository\RepositoryFactory;
use Doctrine\Persistence\ObjectRepository;
use Hyperf\Context\Context;
use Hyperf\Doctrine\EntityManagerFactory;
use Hyperf\Doctrine\ORM\EntityRepository;

/**
 * @internal
 * @see \Doctrine\ORM\Repository\DefaultRepositoryFactory
 */
class CoRepositoryFactory implements RepositoryFactory
{
    public static array $contextKeys = [
        'repository' => 'doctrine.orm.repository',
    ];

    public function getRepository(EntityManagerInterface $entityManager, $entityName): EntityRepository
    {
        $em = EntityManagerFactory::getManagerByWrapped($entityManager);
        $repositoryHash = $entityManager->getClassMetadata($entityName)->getName() . spl_object_id($em);
        if (! Context::has($this->contextKey($repositoryHash))) {
            return Context::set($this->contextKey($repositoryHash), $this->createRepository($em, $entityName));
        }

        return Context::get($this->contextKey($repositoryHash));
    }

    private function contextKey($repositoryHash): string
    {
        return static::$contextKeys['repository'] . '.' . $repositoryHash;
    }

    private function createRepository(
        EntityManagerInterface $entityManager,
        string $entityName
    ): ObjectRepository {
        $metadata = $entityManager->getClassMetadata($entityName);
        $repositoryClassName = $metadata->customRepositoryClassName
            ?: $entityManager->getConfiguration()->getDefaultRepositoryClassName();
        $repository = new $repositoryClassName($entityManager, $metadata);
        if (! $repository instanceof \Hyperf\Doctrine\ORM\EntityRepository) {
            Deprecation::trigger(
                'doctrine/orm',
                'https://github.com/doctrine/orm/pull/9533',
                'Configuring %s as repository class is deprecated because it does not extend %s.',
                $repositoryClassName,
                EntityRepository::class
            );
        }

        return $repository;
    }
}
