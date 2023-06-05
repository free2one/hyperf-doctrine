<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Event;

use Hyperf\Doctrine\Event\OnClear;
use HyperfTest\Mock\Entity\User;

/**
 * @internal
 * @coversNothing
 */
class OnClearTest extends AbstractEventTestCase
{
    public function getTestEvent(): string
    {
        return OnClear::class;
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
        $em->clear();
    }
}
