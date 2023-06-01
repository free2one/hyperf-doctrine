<?php

declare(strict_types=1);

namespace Hyperf\Doctrine\ORM;

use Doctrine\Common\Collections\AbstractLazyCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\LazyCriteriaCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ObjectRepository;
use Hyperf\Doctrine\EntityManagerFactory;

/**
 * @see \Doctrine\ORM\EntityRepository
 */
class EntityRepository implements ObjectRepository, Selectable
{
    protected string $entityName = '';

    protected string $poolName = 'default';

    public function __construct(
        private ?EntityManagerInterface $em = null,
        ?ClassMetadata $class = null,
    ) {
        $this->entityName = $class ? $class->getName() : $this->entityName;
    }

    public function createQueryBuilder($alias, $indexBy = null): QueryBuilder
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select($alias)
            ->from($this->entityName, $alias, $indexBy);
    }

    public function matching(Criteria $criteria): AbstractLazyCollection
    {
        $persister = $this->getEntityManager()->getUnitOfWork()->getEntityPersister($this->entityName);

        return new LazyCriteriaCollection($persister, $criteria);
    }

    public function find($id, $lockMode = null, $lockVersion = null): object|null
    {
        return $this->getEntityManager()->find($this->entityName, $id, $lockMode, $lockVersion);
    }

    public function findAll(): array
    {
        return $this->findBy([]);
    }

    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array
    {
        $persister = $this->getEntityManager()->getUnitOfWork()->getEntityPersister($this->entityName);

        return $persister->loadAll($criteria, $orderBy, $limit, $offset);
    }

    public function findOneBy(array $criteria, array|null $orderBy = null): object|null
    {
        $persister = $this->getEntityManager()->getUnitOfWork()->getEntityPersister($this->entityName);

        return $persister->load($criteria, null, null, [], null, 1, $orderBy);
    }

    public function getClassName(): string
    {
        return $this->entityName;
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->em ?: EntityManagerFactory::getManager($this->poolName);
    }
}
