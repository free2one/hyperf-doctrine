<?php

declare(strict_types=1);

namespace Hyperf\Doctrine\Event;

use Doctrine\Common\EventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;

/**
 * @see Events::preFlush
 */
class PreFlush implements Event
{
    /**
     * @param null|PreFlushEventArgs $eventArgs
     */
    public function setEventArgs(?EventArgs $eventArgs): void
    {
    }
}
