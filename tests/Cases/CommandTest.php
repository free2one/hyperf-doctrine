<?php

declare(strict_types=1);

namespace HyperfTest\Cases;

use Doctrine\ORM\Query\ParserResult;
use Hyperf\Doctrine\ORM\Tools\Console\Command\ClearCache\MetadataCommand;
use Hyperf\Doctrine\ORM\Tools\Console\Command\ClearCache\QueryCommand;
use Hyperf\Doctrine\ORM\Tools\Console\Command\ClearCache\ResultCommand;
use HyperfTest\Mock\Entity\User;
use HyperfTest\Tools\CommandTestHelper;
use HyperfTest\Tools\DataTestHelper;
use ReflectionClass;

/**
 * @internal
 * @coversNothing
 */
class CommandTest extends AbstractTestCase
{
    public function testClearMetadataCache()
    {
        $metadataCache = $this->getManager()->getConfiguration()->getMetadataCache();
        $metaDataFactory = $this->getManager()->getMetadataFactory();
        $metaDataFactory->getMetadataFor(User::class);

        $ref = new ReflectionClass($metaDataFactory);
        $method = $ref->getMethod('getCacheKey');
        $method->setAccessible(true);
        $cacheKey = $method->invoke($metaDataFactory, User::class);
        $cache = $metadataCache->getItem($cacheKey);
        $this->assertTrue($cache->isHit());

        CommandTestHelper::execCmdAndCheck('doctrine:clear-cache:metadata', new MetadataCommand());

        $cache = $metadataCache->getItem($cacheKey);
        $this->assertFalse($cache->isHit());
    }

    public function testClearQueryCache()
    {
        $queryCache = $this->getManager()->getConfiguration()->getQueryCache();
        $query = $this->getManager()->getRepository(User::class)->createQueryBuilder('user')->getQuery();
        $query->getResult();

        $ref = new ReflectionClass($query);
        $method = $ref->getMethod('getQueryCacheId');
        $method->setAccessible(true);
        $cacheKey = $method->invoke($query);
        $cache = $queryCache->getItem($cacheKey);
        $this->assertTrue($cache->isHit());

        CommandTestHelper::execCmdAndCheck('doctrine:clear-cache:query', new QueryCommand());

        $cache = $queryCache->getItem($cacheKey);
        $this->assertFalse($cache->isHit());
    }

    public function testClearResultCache()
    {
        $resultCache = $this->getManager()->getConfiguration()->getResultCache();
        /** @var User[] $users */
        $users = DataTestHelper::initTableDataAndReturn('user', User::class);
        $originUser = reset($users);
        $query = $this->getManager()->createQuery('SELECT user FROM ' . User::class . ' user WHERE user.userName = ?1 AND user.gender = ?2')
            ->setParameters([
                1 => $originUser->getUserName(),
                2 => $originUser->getGender(),
            ])
            ->enableResultCache();
        $user = $query->getResult();
        $this->assertNotEmpty($user);

        $ref = new ReflectionClass($query);
        $property = $ref->getProperty('parserResult');
        $property->setAccessible(true);
        /** @var ParserResult $parserResult */
        $parserResult = $property->getValue($query);
        $paramMappings = $parserResult->getParameterMappings();
        $method = $ref->getMethod('processParameterMappings');
        $method->setAccessible(true);
        [$sqlParams, $types] = $method->invoke($query, $paramMappings);
        $connectionParams = $this->getManager()->getConnection()->getParams();
        unset($connectionParams['platform'], $connectionParams['password'], $connectionParams['url']);
        [$cacheKey, $realKey] = $query->getQueryCacheProfile()->generateCacheKeys($query->getSQL(), $sqlParams, $types, $connectionParams);
        $cache = $resultCache->getItem($cacheKey);
        $this->assertTrue($cache->isHit());

        CommandTestHelper::execCmdAndCheck('doctrine:clear-cache:result', new ResultCommand());

        $cache = $resultCache->getItem($cacheKey);
        $this->assertFalse($cache->isHit());
    }
}
