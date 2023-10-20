<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Query;

use Hyperf\DbConnection\Db;
use HyperfTest\Cases\AbstractTestCase;
use HyperfTest\Mock\Entity\AbstractUser;
use HyperfTest\Mock\Entity\Sub;
use HyperfTest\Mock\Entity\User;
use HyperfTest\Tools\DataTestHelper;
use HyperfTest\Tools\UserTestHelper;
use PHPUnit\Framework\Assert;
use RuntimeException;

/**
 * @internal
 * @coversNothing
 */
class HyperfQueryBuilderTest extends AbstractTestCase
{
    /** @var array<AbstractUser> */
    public static array $users;

    public static array $subs;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$users = DataTestHelper::initTableDataAndReturn('user', User::class);
        self::$subs = DataTestHelper::initTableDataAndReturn('sub', Sub::class);
    }

    public function testValue()
    {
        $res = $this->getManager()->createHyperfQueryBuilder()
            ->from(User::class, 'user')
            ->where('user.userName', '=', 'name1')
            ->value('user.userName');

        $this->assertSame('name1', $res);
    }

    public function testPluck()
    {
        $res = $this->getManager()->createHyperfQueryBuilder()
            ->from(User::class, 'user')
            ->where('user.userName', '=', 'name1')
            ->pluck('user.userName');

        $this->assertSame(['name1'], $res->toArray());
    }

    public function testChunk()
    {
        $this->getManager()->createHyperfQueryBuilder()
            ->select('user')
            ->from(User::class, 'user')
            ->orderBy('user.id')
            ->chunk(10, function ($users) {
                $this->assertCount(10, $users);
            });
    }

    public function testCount()
    {
        $res = $this->getManager()->createHyperfQueryBuilder()
            ->select('user')
            ->from(User::class, 'user')
            ->count('user.id');

        $this->assertSame(count(self::$users), $res);
    }

    public function testMax()
    {
        $res = $this->getManager()->createHyperfQueryBuilder()
            ->select('user')
            ->from(User::class, 'user')
            ->max('user.id');

        $this->assertSame((function () {
            $maxId = 0;
            foreach (self::$users as $user) {
                if ($user->getId() > $maxId) {
                    $maxId = $user->getId();
                }
            }
            return $maxId;
        })(), $res);
    }

    public function testMin()
    {
        $res = $this->getManager()->createHyperfQueryBuilder()
            ->select('user')
            ->from(User::class, 'user')
            ->min('user.id');

        $this->assertSame((function () {
            $minId = 1;
            foreach (self::$users as $user) {
                if ($user->getId() < $minId) {
                    $minId = $user->getId();
                }
            }
            return $minId;
        })(), $res);
    }

    public function testAvg()
    {
        $res = $this->getManager()->createHyperfQueryBuilder()
            ->select('user')
            ->from(User::class, 'user')
            ->avg('user.id');

        $this->assertSame((function () {
            $sum = 0;
            foreach (self::$users as $user) {
                $sum += $user->getId();
            }
            return $sum / count(self::$users);
        })(), (float) sprintf('%.1f', $res));
    }

    public function testSum()
    {
        $res = $this->getManager()->createHyperfQueryBuilder()
            ->select('user')
            ->from(User::class, 'user')
            ->sum('user.id');

        $this->assertSame((function () {
            $sum = 0;
            foreach (self::$users as $user) {
                $sum += $user->getId();
            }
            return $sum;
        })(), (int) $res);
    }

    public function testInRandomOrder()
    {
        $this->expectException(RuntimeException::class);
        $this->getManager()->createHyperfQueryBuilder()
            ->select('user')
            ->from(User::class, 'user')
            ->inRandomOrder()
            ->get();
    }

    public function testExists()
    {
        $this->expectException(RuntimeException::class);
        $this->getManager()->createHyperfQueryBuilder()
            ->select('user')
            ->from(User::class, 'user')
            ->where('user.userName', '=', 'name1')
            ->exists();
    }

    public function testDoesntExist()
    {
        $this->expectException(RuntimeException::class);
        $this->getManager()->createHyperfQueryBuilder()
            ->select('user')
            ->from(User::class, 'user')
            ->where('user.userName', '=', 'name1')
            ->doesntExist();
    }

    public function testDistinct()
    {
        $res = $this->getManager()->createHyperfQueryBuilder()
            ->select('user')
            ->from(User::class, 'user')
            ->distinct()
            ->get();

        $this->assertCount(count(self::$users), $res);
    }

    public function testGetWithoutSelect()
    {
        $res = $this->getManager()->createHyperfQueryBuilder()
            ->from(User::class, 'user')
            ->where('user.userName', '=', 'name1')
            ->get('user');

        $this->assertCount(1, $res);
    }

    public function testLimit()
    {
        $res = $this->getManager()->createHyperfQueryBuilder()
            ->select('user')
            ->from(User::class, 'user')
            ->limit(2)
            ->get();

        $this->assertCount(2, $res);
    }

    public function testOffset()
    {
        $res = $this->getManager()->createHyperfQueryBuilder()
            ->select('user')
            ->from(User::class, 'user')
            ->offset(2)
            ->get();

        $this->assertCount(count(self::$users) - 2, $res);
    }

    public function testForPage()
    {
        $res = $this->getManager()->createHyperfQueryBuilder()
            ->select('user')
            ->from(User::class, 'user')
            ->forPage(2, 2)
            ->get();

        $this->assertCount(2, $res);
    }

    public function testHaving()
    {
        $res = $this->getManager()->createHyperfQueryBuilder()
            ->select('user')
            ->from(User::class, 'user')
            ->whereIn('user.userName', ['name1', 'name2', 'name3', 'name4'])
            ->having('user.gender', '=', 1)
            ->get();

        $this->assertCount(3, $res);
    }

    public function testGroupBy()
    {
        $count = [0 => 0, 1 => 0];
        foreach (static::$users as $user) {
            if ($user->getGender() == 1) {
                ++$count[1];
            } else {
                ++$count[0];
            }
        }

        $res = $this->getManager()->createHyperfQueryBuilder()
            ->select('user.gender', Db::raw('COUNT(user.id) as count'))
            ->from(User::class, 'user')
            ->groupBy(['user.gender'])
            ->get();

        $this->assertCount(2, $res);

        foreach ($res as $value) {
            $this->assertSame($count[$value['gender']], $value['count']);
        }
    }

    public function testWhereBetween()
    {
        $res = $this->getManager()->createHyperfQueryBuilder()
            ->select('user')
            ->from(User::class, 'user')
            ->whereBetween('user.id', [1, 2])
            ->get();

        $this->assertCount(2, $res);
    }

    public function testWhereNotBetween()
    {
        $res = $this->getManager()->createHyperfQueryBuilder()
            ->select('user')
            ->from(User::class, 'user')
            ->whereNotBetween('user.id', [1, 2])
            ->get();

        $this->assertCount(count(self::$users) - 2, $res);
    }

    public function testWhereIn()
    {
        $res = $this->getManager()->createHyperfQueryBuilder()
            ->select('user')
            ->from(User::class, 'user')
            ->whereIn('user.id', [1, 2])
            ->get();

        $this->assertCount(2, $res);
    }

    public function testWhereNotIn()
    {
        $res = $this->getManager()->createHyperfQueryBuilder()
            ->select('user')
            ->from(User::class, 'user')
            ->whereNotIn('user.id', [1, 2])
            ->get();

        $this->assertCount(count(self::$users) - 2, $res);
    }

    public function testWhereExists()
    {
        $res = $this->getManager()->createHyperfQueryBuilder()
            ->select('user')
            ->from(User::class, 'user')
            ->whereExists(function ($query) {
                $query->select('user2')
                    ->from(User::class, 'user2')
                    ->where('user2.userName', '=', 'name1');
            })
            ->get();

        $this->assertCount(count(self::$users), $res);
    }

    public function testOrWhere()
    {
        $res = $this->getManager()->createHyperfQueryBuilder()
            ->select('user')
            ->from(User::class, 'user')
            ->where('user.userName', '=', 'name1')
            ->orWhere('user.userName', '=', 'name2')
            ->get();

        $this->assertCount(2, $res);
    }

    public function testSubQuery1()
    {
        $res = $this->getManager()->createHyperfQueryBuilder()
            ->select('user as info', Db::raw('(select user2.userName from ' . User::class . ' user2 where user2.userName = \'name1\') as name'))
            ->from(User::class, 'user')
            ->where('user.userName', '=', 'name1')
            ->get();

        $this->assertCount(1, $res);

        /** @var array{'info':AbstractUser, 'name':string} $data */
        $data = $res->first();

        $this->assertArrayHasKey('info', $data);
        $this->assertSame('name1', $data['info']->getUserName());
        $this->assertSame(1, $data['info']->getGender());
        $this->assertSame(1, $data['info']->getId());

        $this->assertArrayHasKey('name', $data);
        $this->assertSame('name1', $data['name']);
    }

    public function testSubQuery2()
    {
        $res = $this->getManager()->createHyperfQueryBuilder()
            ->select('user')
            ->from(User::class, 'user')
            ->where('user.userName', '=', 'name1')
            ->where('user.id', '=', Db::raw('(select user2.id from ' . User::class . ' user2 where user2.id = 1)'))
            ->get();

        /** @var AbstractUser $data */
        $data = $res->first();

        $this->assertCount(1, $res);
        $this->assertSame('name1', $data->getUserName());
        $this->assertSame(1, $data->getGender());
        $this->assertSame(1, $data->getId());
    }

    public function testJoin1()
    {
        $builder = $this->getManager()->createHyperfQueryBuilder();
        $res = $builder
            ->select('user', 'sub')
            ->from(User::class, 'user')
            ->join($builder->alias(Sub::class, 'sub'), 'sub.id', '=', 'user.id')
            ->where('user.userName', '=', 'name1')
            ->get();

        $this->assertCount(2, $res);
        $this->assertInstanceOf(AbstractUser::class, $res->get(0));
        $this->assertInstanceOf(Sub::class, $res->get(1));
    }

    public function testLeftJoin1()
    {
        $builder = $this->getManager()->createHyperfQueryBuilder();
        $res = $builder
            ->select('user', 'sub')
            ->from(User::class, 'user')
            ->leftJoin($builder->alias(Sub::class, 'sub'), 'sub.id', '=', 'user.id')
            ->where('user.userName', '=', 'name1')
            ->get();

        $this->assertCount(2, $res);

        $this->assertInstanceOf(AbstractUser::class, $res->get(0));
        $this->assertInstanceOf(Sub::class, $res->get(1));
    }

    public function selectProvider(): array
    {
        return [
            [
                function (AbstractUser $row) {
                    $res = $this->getManager()->createHyperfQueryBuilder()
                        ->select('user')
                        ->from(User::class, 'user')
                        ->where('user.userName', '=', $row->getUserName())
                        ->where('user.gender', '=', $row->getGender())
                        ->get();

                    $this->assertCount(1, $res);
                    $data = $res->toArray();
                    return array_pop($data);
                },
                null,
            ],
            [
                function (AbstractUser $row) {
                    $res = $this->getManager()->createHyperfQueryBuilder()
                        ->select('user.id, user.userName')
                        ->from(User::class, 'user')
                        ->where('user.userName', '=', $row->getUserName())
                        ->where('user.gender', '=', $row->getGender())
                        ->get();

                    $this->assertCount(1, $res);
                    $data = $res->toArray();
                    return array_pop($data);
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
    public function testConcurrentSelect(callable $query, ?callable $compareFun)
    {
        UserTestHelper::queryFromExistingDataAndCompareTwoSides(self::$users, $query, $compareFun);
    }

    public function testPersistAndSelect()
    {
        $em = $this->getManager();
        $res = $em->createHyperfQueryBuilder()
            ->select('user')
            ->from(User::class, 'user')
            ->where('user.userName', '=', 'name20')
            ->get();

        $this->assertCount(1, $res);

        /** @var AbstractUser $user */
        $user = $res->first();
        $user->setUserName('name100');
        $em->persist($user);
        $em->flush();

        $res = $em->createHyperfQueryBuilder()
            ->select('user')
            ->from(User::class, 'user')
            ->where('user.id', '=', $user->getId())
            ->get();
        $this->assertCount(1, $res);
        $this->assertSame($user, $res->first());
    }

    public function testInsert()
    {
        $this->expectException(RuntimeException::class);
        $this->getManager()->createHyperfQueryBuilder()
            ->from(User::class, 'user')
            ->insert(['name' => 'name100', 'gender' => 1]);
    }

    public function testInsertGetId()
    {
        $this->expectException(RuntimeException::class);
        $this->getManager()->createHyperfQueryBuilder()
            ->from(User::class, 'user')
            ->insertGetId(['name' => 'name100', 'gender' => 1]);
    }

    public function testUpdate()
    {
        $res = $this->getManager()->createHyperfQueryBuilder()
            ->from(User::class, 'user')
            ->where('user.userName', '=', 'name1')
            ->update(['user.userName' => 'name1001']);

        $this->assertSame(1, $res);
    }

    public function testLockForUpdate()
    {
        $this->expectException(RuntimeException::class);
        $this->getManager()->createHyperfQueryBuilder()
            ->from(User::class, 'user')
            ->lockForUpdate()
            ->get();
    }

    public function testIncrement()
    {
        $res = $this->getManager()->createHyperfQueryBuilder()
            ->from(User::class, 'user')
            ->where('user.id', '=', 1)
            ->increment('user.gender');

        $this->assertSame(1, $res);
    }

    public function testDecrement()
    {
        $res = $this->getManager()->createHyperfQueryBuilder()
            ->from(User::class, 'user')
            ->where('user.id', '=', 1)
            ->decrement('user.gender');

        $this->assertSame(1, $res);
    }

    public function testDelete()
    {
        $res = $this->getManager()->createHyperfQueryBuilder()
            ->from(User::class, 'user')
            ->where('user.userName', '=', 'name2')
            ->delete();

        $this->assertSame(1, $res);
    }
}
