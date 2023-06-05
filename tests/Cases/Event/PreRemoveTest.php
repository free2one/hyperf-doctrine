<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Event;

use Hyperf\Doctrine\Event\PreRemove;
use HyperfTest\Mock\Entity\User;

/**
 * @internal
 * @coversNothing
 */
class PreRemoveTest extends AbstractEventTestCase
{
    public function getTestEvent(): string
    {
        return PreRemove::class;
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
        $em->remove($user);
    }
}
