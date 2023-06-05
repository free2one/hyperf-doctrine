<?php

declare(strict_types=1);

namespace Hyperf\Doctrine\Event;

use Doctrine\Common\EventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;

/**
 * @see Events::postPersist
 */
class PostPersist implements Event
{
    use BaseEventArgs;

    /**
     * @param null|PostPersistEventArgs $eventArgs
     */
    public function setEventArgs(?EventArgs $eventArgs): void
    {
        $this->object = $eventArgs->getObject();
    }
}
