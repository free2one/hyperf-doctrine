<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Query;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use Hyperf\Context\ApplicationContext;
use Hyperf\Doctrine\EntityManagerFactory;
use HyperfTest\Cases\AbstractTestCase;
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
class QueryBuilderTest extends AbstractTestCase
{
    public function queryBuilderProvider(): array
    {
        return [
            [
                'builders' => [
                    [
                        function () {
                            return EntityManagerFactory::getManager()->createQueryBuilder()->select('user')->from(User::class, 'user');
                        },
                        User::class,
                    ],
                    [
                        function () {
                            return EntityManagerFactory::getManager()->getRepository(User::class)->createQueryBuilder('user');
                        },
                        User::class,
                    ],
                    [
                        function () {
                            return ApplicationContext::getContainer()->get(UserRepository::class)->createQueryBuilder('user');
                        },
                        UserWithRepository::class,
                    ],
                ],
                'queries' => [
                    function (AbstractUser $row, $queryBuilder) {
                        /** @var callable<QueryBuilder> $queryBuilder $res */
                        $res = $queryBuilder()
                            ->where('user.userName = ?1')
                            ->andWhere('user.gender = ?2')
                            ->setParameter(1, $row->getUserName())
                            ->setParameter(2, $row->getGender())
                            ->getQuery()
                            ->getResult();
                        $this->assertCount(1, $res);
                        return array_pop($res);
                    },
                    function (AbstractUser $row, $queryBuilder) {
                        /** @var callable<QueryBuilder> $queryBuilder $res */
                        $builder = $queryBuilder();
                        $res = $builder
                            ->where(
                                $builder->expr()->eq('user.userName', '?1')
                            )
                            ->andWhere(
                                $builder->expr()->eq('user.gender', '?2')
                            )
                            ->setParameter(1, $row->getUserName())
                            ->setParameter(2, $row->getGender())
                            ->getQuery()
                            ->getResult();
                        $this->assertCount(1, $res);
                        return array_pop($res);
                    },
                    function (AbstractUser $row, $queryBuilder) {
                        /** @var callable<QueryBuilder> $queryBuilder $res */
                        $res = $queryBuilder()
                            ->add(
                                'where',
                                new Expr\Andx([
                                    new Expr\Comparison('user.userName', '=', '?1'),
                                    new Expr\Comparison('user.gender', '=', '?2'),
                                ])
                            )
                            ->setParameters(
                                new ArrayCollection([
                                    new Parameter(1, $row->getUserName()),
                                    new Parameter(2, $row->getGender()),
                                ])
                            )
                            ->getQuery()
                            ->getResult();
                        $this->assertCount(1, $res);
                        return array_pop($res);
                    },
                ],
            ],
        ];
    }

    /**
     * @dataProvider queryBuilderProvider
     */
    public function testCoroutineUpdate(array $builders, array $queries)
    {
        foreach ($builders as $builder) {
            foreach ($queries as $query) {
                $users = DataTestHelper::initTableDataAndReturn('user', $builder[1]);

                $originUser = reset($users);
                UserTestHelper::queryAndUpdateWithNoConfusion(
                    $originUser,
                    function ($row) use ($query, $builder) {
                        return $query($row, $builder[0]);
                    }
                );

                DataTestHelper::truncateTable('user');
            }
        }
    }
}
