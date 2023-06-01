<?php

declare(strict_types=1);

namespace HyperfTest\Cases;

use Hyperf\Doctrine\EntityManagerFactory;
use Hyperf\Doctrine\ORM\EntityManager;
use HyperfTest\Mock\Entity\AbstractUser;
use HyperfTest\Tools\DataTestHelper;
use PHPUnit\Framework\TestCase;

/**
 * Class AbstractTestCase.
 */
abstract class AbstractTestCase extends TestCase
{
    protected static array $initTableData = [];

    public static function setUpBeforeClass(): void
    {
        self::getManager()->getConnection()->executeStatement(
            "CREATE TABLE IF NOT EXISTS  `user` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `name` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
                `gender` tinyint unsigned NOT NULL,
                `version` int unsigned NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
        );

        DataTestHelper::truncateTable('user');
    }

    public function tearDown(): void
    {
        parent::tearDown();
        self::getManager()->clear();
    }

    protected static function getManager($poolName = 'default'): EntityManager
    {
        return EntityManagerFactory::getManager($poolName);
    }

    /**
     * @param AbstractUser[] $user1
     * @param AbstractUser[] $user2
     */
    protected function compareUsers(array $user1, array $user2): void
    {
        $this->assertNotEmpty($user1);
        $this->assertNotEmpty($user2);
        $this->assertSameSize($user1, $user2);

        /**
         * @param AbstractUser[] $u1
         * @param AbstractUser[] $u2
         */
        $compareFun = function ($u1, $u2) {
            $temp = $u2;
            foreach ($u1 as $value) {
                $this->assertArrayHasKey($value->getId(), $temp);
                $this->assertObjectEquals($value, $temp[$value->getId()]);
                unset($temp[$value->getId()]);
            }
            $this->assertEmpty($temp);
        };
        $compareFun($user1, $user2);
        $compareFun($user2, $user1);
    }
}
