<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Event;

use Hyperf\Doctrine\Event\PrePersist;
use HyperfTest\Mock\Entity\User;

/**
 * @internal
 * @coversNothing
 */
class PrePersistTest extends AbstractEventTestCase
{
    public function getTestEvent(): string
    {
        return PrePersist::class;
    }

    public function test()
    {
        $em = $this->getManager();
        $user = new User();
        $user
            ->setUserName('eventUser')
            ->setGender(1)
            ->setVersion(0);
        $em->persist($user);
    }
}
