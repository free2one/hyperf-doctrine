<?php

declare(strict_types=1);

namespace HyperfTest\Mock\Repository;

use Hyperf\Doctrine\ORM\EntityRepository;
use HyperfTest\Mock\Entity\UserWithRepository;

class UserRepository extends EntityRepository
{
    protected string $entityName = UserWithRepository::class;

    public function findByUserName(string $userName): array
    {
        return $this->createQueryBuilder('user')
            ->where('user.userName = ?1')
            ->setParameter(1, $userName)
            ->getQuery()
            ->getResult();
    }
}
