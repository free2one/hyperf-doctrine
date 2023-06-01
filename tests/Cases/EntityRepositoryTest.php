<?php

declare(strict_types=1);

namespace HyperfTest\Cases;

use Doctrine\Common\Collections\Criteria;
use Hyperf\Coroutine\Parallel;
use Hyperf\Doctrine\ORM\EntityRepository;
use HyperfTest\Mock\Entity\AbstractUser;
use HyperfTest\Mock\Entity\User;
use HyperfTest\Tools\DataTestHelper;
use HyperfTest\Tools\UserTestHelper;

/**
 * @internal
 * @coversNothing
 */
class EntityRepositoryTest extends AbstractTestCase
{
    public function testSameCoroutine()
    {
        $this->assertSame(
            spl_object_id($this->getManager()->getRepository(User::class)),
            spl_object_id($this->getManager()->getRepository(User::class))
        );
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(EntityRepository::class, $this->getManager()->getRepository(User::class));
    }

    public function testGetClassName()
    {
        $this->assertSame($this->getManager()->getRepository(User::class)->getClassName(), User::class);
    }

    public function testGetEntityManagerInSameCoroutine()
    {
        $this->assertSame(
            spl_object_id($this->getManager()),
            spl_object_id($this->getManager()->getRepository(User::class)->getEntityManager())
        );
    }

    public function testGetManagerInDifferentCoroutine()
    {
        $parallel = new Parallel();
        $parallel->add(function () {
            return spl_object_id($this->getManager()->getRepository(User::class)->getEntityManager());
        });
        $res = $parallel->wait();
        $this->assertNotSame(
            spl_object_id($this->getManager()),
            $res[0]
        );
    }

    public function coroutineUpdateProvider(): array
    {
        return [
            [
                function (AbstractUser $user) {
                    return $this->getManager()->getRepository(User::class)->createQueryBuilder('user')
                        ->where('user.userName = ?1')
                        ->setParameter(1, $user->getUserName())
                        ->getQuery()
                        ->getSingleResult();
                },
                User::class,
            ],
            [
                function (AbstractUser $user) {
                    return $this->getManager()->getRepository(User::class)->matching(
                        Criteria::create()
                            ->where(Criteria::expr()->eq('userName', $user->getUserName()))
                            ->setMaxResults(1)
                    )->first();
                },
                User::class,
            ],
            [
                function (AbstractUser $user) {
                    return $this->getManager()->getRepository(User::class)->find($user->getId());
                },
                User::class,
            ],
            [
                function (AbstractUser $user) {
                    $users = $this->getManager()->getRepository(User::class)->findBy([
                        'userName' => $user->getUserName(),
                    ]);
                    $this->assertCount(1, $users);
                    return array_pop($users);
                },
                User::class,
            ],
            [
                function (AbstractUser $user) {
                    return $this->getManager()->getRepository(User::class)->findOneBy([
                        'userName' => $user->getUserName(),
                    ]);
                },
                User::class,
            ],
        ];
    }

    /**
     * @dataProvider coroutineUpdateProvider
     * @param callable<User> $query
     */
    public function testCoroutineUpdate(callable $query, string $entityClass)
    {
        /** @var User[] $users */
        $users = DataTestHelper::initTableDataAndReturn('user', $entityClass);

        $originUser = reset($users);
        UserTestHelper::queryAndUpdateWithNoConfusion(
            $originUser,
            $query
        );

        DataTestHelper::truncateTable('user');
    }
}
