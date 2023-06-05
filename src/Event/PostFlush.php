<?php

declare(strict_types=1);

namespace Hyperf\Doctrine\Event;

use Doctrine\Common\EventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;

/**
 * @see Events::postFlush
 */
class PostFlush implements Event
{
    /**
     * @param null|PostFlushEventArgs $eventArgs
     */
    public function setEventArgs(?EventArgs $eventArgs): void
    {
    }
}
