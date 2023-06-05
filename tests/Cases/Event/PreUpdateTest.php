<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Event;

use Hyperf\Doctrine\Event\PreUpdate;
use HyperfTest\Mock\Entity\User;

/**
 * @internal
 * @coversNothing
 */
class PreUpdateTest extends AbstractEventTestCase
{
    public function getTestEvent(): string
    {
        return PreUpdate::class;
    }

    /**
     * @param PreUpdate $event
     */
    public function process(object $event): void
    {
        parent::process($event);
        $this->assertNotEmpty($event->getEntityChangeSet());
        $this->assertTrue($event->hasChangedField('userName'));
        $this->assertEquals('eventUser', $event->getOldValue('userName'));
        $this->assertEquals('eventUser2', $event->getNewValue('userName'));
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
