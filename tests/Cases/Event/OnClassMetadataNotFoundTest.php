<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Event;

use Doctrine\Persistence\Mapping\MappingException;
use Hyperf\Doctrine\Event\OnClassMetadataNotFound;

/**
 * @internal
 * @coversNothing
 */
class OnClassMetadataNotFoundTest extends AbstractEventTestCase
{
    public function getTestEvent(): string
    {
        return OnClassMetadataNotFound::class;
    }

    /**
     * @param OnClassMetadataNotFound $event
     */
    public function process(object $event): void
    {
        parent::process($event);
        $this->assertEquals('UserNotFound', $event->getClassName());
    }

    public function test()
    {
        $this->expectException(MappingException::class);
        $em = $this->getManager();
        $em->find('UserNotFound', 1);
    }
}
