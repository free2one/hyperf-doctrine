<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Event;

use Hyperf\Doctrine\Event\OnFlush;

/**
 * @internal
 * @coversNothing
 */
class OnFlushTest extends AbstractEventTestCase
{
    public function getTestEvent(): string
    {
        return OnFlush::class;
    }

    public function test()
    {
        $em = $this->getManager();
        $em->flush();
    }
}
