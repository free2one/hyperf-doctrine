<?php

declare(strict_types=1);

namespace HyperfTest\Cases;

use Hyperf\Context\ApplicationContext;
use Hyperf\Coroutine\Parallel;
use HyperfTest\Mock\Entity\AbstractUser;
use HyperfTest\Mock\Entity\User;
use HyperfTest\Mock\Entity\UserWithRepository;
use HyperfTest\Mock\Repository\UserRepository;
use HyperfTest\Tools\DataTestHelper;
use HyperfTest\Tools\UserTestHelper;

/**
 * @internal
 * @coversNothing
 */
class CustomRepositoryTest extends AbstractTestCase
{
    public function testGetEntityManager()
    {
        $this->assertSame(
            spl_object_id($this->getManager()),
            spl_object_id(ApplicationContext::getContainer()->get(UserRepository::class)->getEntityManager())
        );

        $parallel = new Parallel();
        $parallel->add(function () {
            return spl_object_id(ApplicationContext::getContainer()->get(UserRepository::class)->getEntityManager());
        });
        $res = $parallel->wait();
        $this->assertNotSame(
            spl_object_id($this->getManager()),
            $res[0]
        );
    }

    public static function coroutineUpdateProvider(): array
    {
        return [
            [
                function (AbstractUser $user) {
                    $res = ApplicationContext::getContainer()->get(UserRepository::class)->findByUserName($user->getUserName());
                    self::assertCount(1, $res);
                    return array_pop($res);
                },
                UserWithRepository::class,
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
