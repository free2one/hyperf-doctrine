<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Event;

use Hyperf\Doctrine\Event\OnClear;
use Hyperf\Event\Contract\ListenerInterface;
use HyperfTest\Cases\AbstractTestCase;
use HyperfTest\Tools\EventTestHelper;

abstract class AbstractEventTestCase extends AbstractTestCase implements ListenerInterface
{
    protected function setUp(): void
    {
        EventTestHelper::addListener($this);
        parent::setUp();
    }

    protected function tearDown(): void
    {
        EventTestHelper::removeListener($this);
        parent::tearDown();
    }

    public function listen(): array
    {
        return [
            $this->getTestEvent(),
        ];
    }

    /**
     * @param OnClear $event
     */
    public function process(object $event): void
    {
        $this->assertNotEmpty($event);
        $this->assertSame($event::class, $this->getTestEvent());
    }

    abstract public function getTestEvent(): string;
}
