<?php

declare(strict_types=1);

namespace Hyperf\Doctrine\Event;

use Doctrine\Common\EventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;

/**
 * @see Events::preRemove
 */
class PreRemove implements Event
{
    use BaseEventArgs;

    /**
     * @param null|PreRemoveEventArgs $eventArgs
     */
    public function setEventArgs(?EventArgs $eventArgs): void
    {
        $this->object = $eventArgs->getObject();
    }
}
