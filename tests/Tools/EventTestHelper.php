<?php

declare(strict_types=1);

namespace HyperfTest\Tools;

use Hyperf\Context\ApplicationContext;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Event\ListenerProvider;
use Psr\EventDispatcher\ListenerProviderInterface;

class EventTestHelper
{
    private static array $originListeners = [];

    public static function addListener(ListenerInterface $listener): void
    {
        /** @var ListenerProvider $listenerProvider */
        $listenerProvider = ApplicationContext::getContainer()->get(ListenerProviderInterface::class);
        self::$originListeners[spl_object_id($listener)] = $listenerProvider->listeners;
        foreach ($listener->listen() as $event) {
            $listenerProvider->on($event, [$listener, 'process']);
        }
    }

    public static function removeListener(ListenerInterface $listener): void
    {
        if (! isset(self::$originListeners[spl_object_id($listener)])) {
            return;
        }

        /** @var ListenerProvider $listenerProvider */
        $listenerProvider = ApplicationContext::getContainer()->get(ListenerProviderInterface::class);
        $listenerProvider->listeners = self::$originListeners[spl_object_id($listener)];
        unset(self::$originListeners[spl_object_id($listener)]);
    }
}
