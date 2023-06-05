<?php

declare(strict_types=1);

namespace Hyperf\Doctrine\Event;

use Doctrine\Common\EventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;

/**
 * @see Events::postRemove
 */
class PostRemove implements Event
{
    use BaseEventArgs;

    /**
     * @param null|PostRemoveEventArgs $eventArgs
     */
    public function setEventArgs(?EventArgs $eventArgs): void
    {
        $this->object = $eventArgs->getObject();
    }
}
