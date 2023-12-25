<?php

declare(strict_types=1);

namespace Cases;

use HyperfTest\Cases\AbstractTestCase;
use HyperfTest\Mock\Entity\AbstractUser;
use HyperfTest\Mock\Entity\User;
use HyperfTest\Tools\DataTestHelper;
use RuntimeException;

/**
 * @internal
 * @coversNothing
 */
class TransactionTest extends AbstractTestCase
{
    /** @var array<AbstractUser> */
    public static array $users;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$users = DataTestHelper::initTableDataAndReturn('user', User::class);
    }

    public function testWrapInTransaction()
    {
        $em = $this->getManager();
        $name = 'newName';
        $em->wrapInTransaction(function () use ($em, $name) {
            $user = $em->find(User::class, 1);
            $user->setUserName($name);
            $em->persist($user);
        });

        $user = $em->find(User::class, 1);
        $this->assertSame($name, $user->getUserName());
    }

    public function testWrapInTransactionError()
    {
        $em = $this->getManager();
        $name = 'newName';

        $this->expectException(RuntimeException::class);
        try {
            $em->wrapInTransaction(function () use ($em, $name) {
                $user = $em->find(User::class, 2);
                $user->setUserName($name);
                $em->persist($user);
                throw new RuntimeException('test');
            });
        } catch (RuntimeException $exception) {
            $user = $em->find(User::class, 2);
            $this->assertNotSame($name, $user->getUserName());
            throw $exception;
        }
    }
}
