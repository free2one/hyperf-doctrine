<?php

declare(strict_types=1);

namespace HyperfTest\Cases;

use HyperfTest\Mock\Entity\UserWithSoftDeleteable;
use HyperfTest\Tools\DataTestHelper;

/**
 * @internal
 * @coversNothing
 */
class SoftDeleteTest extends AbstractTestCase
{
    public function testSoftDelete()
    {
        /** @var UserWithSoftDeleteable[] $users */
        $users = DataTestHelper::initTableDataAndReturn('user', UserWithSoftDeleteable::class);

        $em = $this->getManager();
        foreach ($users as $user) {
            $entity = $em->find(UserWithSoftDeleteable::class, $user->getId());
            $em->remove($entity);
            $em->flush();
        }

        foreach ($users as $user) {
            $entity = $em->createQueryBuilder()
                ->select('user')
                ->from(UserWithSoftDeleteable::class, 'user')
                ->where('user.userName = ?1')
                ->setParameter(1, $user->getUserName())
                ->getQuery()
                ->getResult();
            $this->assertEmpty($entity);
        }

        $em->getFilters()->disable('soft-deleteable');
        foreach ($users as $user) {
            $entity = $em->createQueryBuilder()
                ->select('user')
                ->from(UserWithSoftDeleteable::class, 'user')
                ->where('user.userName = ?1')
                ->setParameter(1, $user->getUserName())
                ->getQuery()
                ->getResult();
            $this->assertNotEmpty($entity);
        }
    }
}
