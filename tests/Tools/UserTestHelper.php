<?php

declare(strict_types=1);

namespace HyperfTest\Tools;

use Hyperf\Coroutine\Exception\ParallelExecutionException;
use Hyperf\Coroutine\Parallel;
use Hyperf\Doctrine\EntityManagerFactory;
use HyperfTest\Mock\Entity\AbstractUser;
use PHPUnit\Framework\Assert;

/**
 * @internal
 */
class UserTestHelper
{
    public static function queryAndUpdateWithNoConfusion(AbstractUser $originUser, callable $query): void
    {
        $user1 = $query($originUser);
        Assert::assertNotEmpty($user1);
        Assert::assertObjectEquals($user1, $originUser);
        $user1->setUserName($user1->getUserName() . 'v1');
        EntityManagerFactory::getManager()->persist($user1);

        try {
            $parallel = new Parallel();
            $parallel->add(function () use ($originUser, $user1, $query) {
                $em = EntityManagerFactory::getManager();
                $user2 = $query($originUser);
                Assert::assertObjectEquals($user2, $originUser);
                Assert::assertObjectEquals($user1, $user2, 'notEquals');
                $user2->setGender($user2->getGender() + 1);
                $em->persist($user2);
                $em->flush();
            });
            $parallel->wait();
        } catch (ParallelExecutionException $e) {
            foreach ($e->getThrowables() as $throwable) {
                throw $throwable;
            }
        }

        EntityManagerFactory::getManager()->flush();
        Assert::assertEquals(spl_object_id(EntityManagerFactory::getManager()->find(get_class($originUser), $originUser->getId())), spl_object_id($user1));

        EntityManagerFactory::getManager()->clear();
        Assert::assertNotEquals(spl_object_id(EntityManagerFactory::getManager()->find(get_class($originUser), $originUser->getId())), spl_object_id($user1));
        Assert::assertObjectEquals(EntityManagerFactory::getManager()->find(get_class($originUser), $originUser->getId()), $user1, 'notEquals');
    }

    public static function queryFromExistingDataAndCompareTwoSides($existingData, callable $query, ?callable $customCompareFun = null): void
    {
        $parallel = new Parallel();
        foreach ($existingData as $id => $row) {
            $parallel->add(function () use ($row, $query) {
                return $query($row);
            }, $id);
        }
        try {
            $res = $parallel->wait();
            self::compareTwoSides($existingData, $res, $customCompareFun);
        } catch (ParallelExecutionException $e) {
            foreach ($e->getThrowables() as $throwable) {
                throw $throwable;
            }
        }
    }

    public static function compareTwoSides(array $side1, array $side2, ?callable $customCompareFun = null): void
    {
        $compareFun = $customCompareFun ?: function ($u1, $u2) {
            /**
             * @var AbstractUser[] $u1
             * @var AbstractUser[] $u2
             */
            $temp = $u2;
            foreach ($u1 as $value) {
                Assert::assertArrayHasKey($value->getId(), $temp);
                Assert::assertObjectEquals($value, $temp[$value->getId()]);
                unset($temp[$value->getId()]);
            }
            Assert::assertEmpty($temp);
        };

        Assert::assertNotEmpty($side1);
        Assert::assertNotEmpty($side2);
        Assert::assertSameSize($side1, $side2);
        $compareFun($side1, $side2);
        $compareFun($side2, $side1);
    }
}
