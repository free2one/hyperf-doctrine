<?php

declare(strict_types=1);

namespace Hyperf\Doctrine\EventManager;

use Doctrine\ORM\Events;
use Hyperf\Doctrine\Event\LoadClassMetadata;
use Hyperf\Doctrine\Event\OnClassMetadataNotFound;
use Hyperf\Doctrine\Event\OnClear;
use Hyperf\Doctrine\Event\OnFlush;
use Hyperf\Doctrine\Event\PostFlush;
use Hyperf\Doctrine\Event\PostLoad;
use Hyperf\Doctrine\Event\PostPersist;
use Hyperf\Doctrine\Event\PostRemove;
use Hyperf\Doctrine\Event\PostUpdate;
use Hyperf\Doctrine\Event\PreFlush;
use Hyperf\Doctrine\Event\PrePersist;
use Hyperf\Doctrine\Event\PreRemove;
use Hyperf\Doctrine\Event\PreUpdate;

/**
 * @internal
 */
class EventMapper
{
    private static array $eventMap = [
        Events::preRemove => PreRemove::class,
        Events::postRemove => PostRemove::class,
        Events::prePersist => PrePersist::class,
        Events::postPersist => PostPersist::class,
        Events::preUpdate => PreUpdate::class,
        Events::postUpdate => PostUpdate::class,
        Events::postLoad => PostLoad::class,
        Events::loadClassMetadata => LoadClassMetadata::class,
        Events::onClassMetadataNotFound => OnClassMetadataNotFound::class,
        Events::preFlush => PreFlush::class,
        Events::onFlush => OnFlush::class,
        Events::postFlush => PostFlush::class,
        Events::onClear => OnClear::class,
    ];

    public static function getEvent(string $doctrineEvent)
    {
        return self::$eventMap[$doctrineEvent] ?? null;
    }
}
