<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Event;

use Hyperf\Doctrine\Event\PreFlush;

/**
 * @internal
 * @coversNothing
 */
class PreFlushTest extends AbstractEventTestCase
{
    public function getTestEvent(): string
    {
        return PreFlush::class;
    }

    public function test()
    {
        $em = $this->getManager();
        $em->flush();
    }
}
