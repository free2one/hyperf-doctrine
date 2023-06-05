<?php

declare(strict_types=1);

namespace Hyperf\Doctrine\Event;

use Doctrine\Common\EventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;

/**
 * @see Events::prePersist
 */
class PrePersist implements Event
{
    use BaseEventArgs;

    /**
     * @param null|PrePersistEventArgs $eventArgs
     */
    public function setEventArgs(?EventArgs $eventArgs): void
    {
        $this->object = $eventArgs->getObject();
    }
}
