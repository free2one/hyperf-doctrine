<?php

declare(strict_types=1);

namespace Hyperf\Doctrine\ORM;

use Doctrine\ORM\Decorator\EntityManagerDecorator;
use Doctrine\ORM\EntityRepository;
use Hyperf\Doctrine\Query\Grammars\DqlGrammar;
use Hyperf\Doctrine\Query\HyperfQueryBuilder;

class EntityManager extends EntityManagerDecorator
{
    /**
     * @param mixed $className
     * @return EntityRepository
     */
    public function getRepository($className)
    {
        return $this->wrapped->getRepository($className);
    }

    public function createHyperfQueryBuilder(): HyperfQueryBuilder
    {
        return new HyperfQueryBuilder($this, $this->getConnection()->getHyperfConnection(), new DqlGrammar());
    }
}
