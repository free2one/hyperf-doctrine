<?php

declare(strict_types=1);

namespace HyperfTest\Cases;

use Hyperf\Doctrine\EntityManagerFactory;
use HyperfTest\Mock\Entity\AbstractUser;
use HyperfTest\Mock\Entity\User;
use HyperfTest\Tools\DataTestHelper;

/**
 * @internal
 * @coversNothing
 */
class CustomFunctionTest extends AbstractTestCase
{
    /** @var array<AbstractUser> */
    public static array $users;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$users = DataTestHelper::initTableDataAndReturn('user', User::class);
    }

    public function testCustomStringFunction()
    {
        foreach (self::$users as $user) {
            $em = EntityManagerFactory::getManager()->createQueryBuilder();
            $query = $em
                ->select('TEST_SUBSTRING(user.userName, 1, 5) as shortName')
                ->from(User::class, 'user')
                ->where('user.id = ?1')
                ->setParameter(1, $user->getId())
                ->getQuery();
            $res = $query->getOneOrNullResult();

            self::assertNotNull($res);
            self::assertSame(substr($user->getUserName(), 0, 5), $res['shortName']);
        }
    }

    public function testCustomNumericFunction()
    {
        $em = EntityManagerFactory::getManager()->createQueryBuilder();
        $query = $em
            ->select('TEST_RAND() as randomValue')
            ->from(User::class, 'user')
            ->where('user.id = 1')
            ->getQuery();
        $res = $query->getOneOrNullResult();

        self::assertNotNull($res);
        self::assertIsFloat($res['randomValue']);
    }

    public function testCustomDatetimeFunction()
    {
        $em = EntityManagerFactory::getManager()->createQueryBuilder();
        $query = $em
            ->select('TEST_NOW() as now')
            ->from(User::class, 'user')
            ->where('user.id = 1')
            ->getQuery();
        $res = $query->getOneOrNullResult();

        self::assertNotNull($res);
        self::assertNotFalse(strtotime($res['now']));
    }
}
