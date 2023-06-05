<?php

declare(strict_types=1);

namespace Hyperf\Doctrine\Event;

use Doctrine\Common\EventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;

/**
 * @see Events::postUpdate
 */
class PostUpdate implements Event
{
    use BaseEventArgs;

    /**
     * @param null|PostUpdateEventArgs $eventArgs
     */
    public function setEventArgs(?EventArgs $eventArgs): void
    {
        $this->object = $eventArgs->getObject();
    }
}
