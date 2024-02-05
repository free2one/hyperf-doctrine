<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Query;

use Hyperf\Doctrine\EntityManagerFactory;
use HyperfTest\Cases\AbstractTestCase;
use HyperfTest\Mock\Entity\AbstractUser;
use HyperfTest\Mock\Entity\User;
use HyperfTest\Tools\DataTestHelper;
use HyperfTest\Tools\UserTestHelper;
use PHPUnit\Framework\Assert;

/**
 * @internal
 * @coversNothing
 */
class DoctrineQueryLanguageTest extends AbstractTestCase
{
    public static array $users;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$users = DataTestHelper::initTableDataAndReturn('user', User::class);
    }

    public static function selectProvider(): array
    {
        return [
            [
                function (AbstractUser $row) {
                    $res = EntityManagerFactory::getManager()
                        ->createQuery('SELECT user FROM ' . User::class . ' user WHERE user.userName = ?1 AND user.gender = ?2')
                        ->setParameters([
                            1 => $row->getUserName(),
                            2 => $row->getGender(),
                        ])
                        ->getResult();
                    self::assertCount(1, $res);
                    return array_pop($res);
                },
                null,
            ],
            [
                function (AbstractUser $row) {
                    $res = EntityManagerFactory::getManager()
                        ->createQuery('SELECT user.id, user.userName FROM ' . User::class . ' user WHERE user.userName = ?1')
                        ->setParameter(1, $row->getUserName())
                        ->getResult();
                    self::assertCount(1, $res);
                    return array_pop($res);
                },
                function ($u1, $u2) {
                    foreach ($u1 as $value) {
                        if ($value instanceof AbstractUser) {
                            $id = $value->getId();
                            Assert::assertArrayHasKey($id, $u2);
                            Assert::assertSame($value->getId(), $u2[$id]['id']);
                            Assert::assertSame($value->getUserName(), $u2[$id]['userName']);
                        } else {
                            $id = $value['id'];
                            Assert::assertArrayHasKey($id, $u2);
                            /* @var AbstractUser[] $u2 */
                            Assert::assertSame($value['id'], $u2[$id]->getId());
                            Assert::assertSame($value['userName'], $u2[$id]->getUserName());
                        }
                        unset($u2[$id]);
                    }
                    Assert::assertEmpty($u2);
                },
            ],
        ];
    }

    /**
     * @dataProvider selectProvider
     */
    public function testSelectQuery(callable $query, ?callable $compareFun)
    {
        UserTestHelper::queryFromExistingDataAndCompareTwoSides(self::$users, $query, $compareFun);
    }
}
