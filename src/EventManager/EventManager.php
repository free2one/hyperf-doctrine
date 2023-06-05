<?php

declare(strict_types=1);

namespace Hyperf\Doctrine\EventManager;

use Doctrine\Common\EventArgs;
use Doctrine\Common\EventManager as DoctrineEventManager;
use Hyperf\Doctrine\Event\Event;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
class EventManager extends DoctrineEventManager
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function dispatchEvent($eventName, ?EventArgs $eventArgs = null): void
    {
        $eventClass = EventMapper::getEvent($eventName);
        if ($eventClass) {
            /** @var Event $eventObj */
            $eventObj = new $eventClass();
            $eventObj->setEventArgs($eventArgs);
            $this->eventDispatcher->dispatch($eventObj);
        }

        parent::dispatchEvent($eventName, $eventArgs);
    }

    public function hasListeners($event): bool
    {
        $eventClass = EventMapper::getEvent($event);
        if (! empty($eventClass)) {
            return true;
        }

        return parent::hasListeners($event);
    }
}
