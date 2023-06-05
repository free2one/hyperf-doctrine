<?php

declare(strict_types=1);

namespace Hyperf\Doctrine\Event;

use Doctrine\Common\EventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;

/**
 * @see Events::onFlush
 */
class OnFlush implements Event
{
    /**
     * @param null|OnFlushEventArgs $eventArgs
     */
    public function setEventArgs(?EventArgs $eventArgs): void
    {
    }
}
