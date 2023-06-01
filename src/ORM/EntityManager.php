<?php

declare(strict_types=1);

namespace Hyperf\Doctrine\ORM;

use Doctrine\ORM\Decorator\EntityManagerDecorator;
use Doctrine\ORM\EntityRepository;

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
}
