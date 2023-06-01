<?php

declare(strict_types=1);

namespace HyperfTest\Cases;

use Hyperf\Coroutine\Parallel;
use Hyperf\Doctrine\EntityManagerFactory;

/**
 * @internal
 * @coversNothing
 */
class EntityManagerFactoryTest extends AbstractTestCase
{
    public function testGetManagerInSameCoroutine()
    {
        $this->assertSame(spl_object_id(EntityManagerFactory::getManager()), spl_object_id(EntityManagerFactory::getManager()));
    }

    public function testGetManagerInDifferentCoroutine()
    {
        $parallel = new Parallel();
        for ($i = 0; $i < 2; ++$i) {
            $parallel->add(function () {
                return spl_object_id(EntityManagerFactory::getManager());
            });
        }
        $res = $parallel->wait();
        $this->assertNotSame($res[0], $res[1]);
    }
}
