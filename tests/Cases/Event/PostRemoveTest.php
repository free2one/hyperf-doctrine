<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Event;

use Hyperf\Doctrine\Event\PostRemove;
use HyperfTest\Mock\Entity\User;

/**
 * @internal
 * @coversNothing
 */
class PostRemoveTest extends AbstractEventTestCase
{
    public function getTestEvent(): string
    {
        return PostRemove::class;
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
        $em->flush();
        $em->remove($user);
        $em->flush();
    }
}
