<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Event;

use Hyperf\Doctrine\Event\PostUpdate;
use HyperfTest\Mock\Entity\User;

/**
 * @internal
 * @coversNothing
 */
class PostUpdateTest extends AbstractEventTestCase
{
    public function getTestEvent(): string
    {
        return PostUpdate::class;
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
        $user->setUserName('eventUser2');
        $em->flush();
    }
}
