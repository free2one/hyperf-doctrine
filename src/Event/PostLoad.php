<?php

declare(strict_types=1);

namespace Hyperf\Doctrine\Event;

use Doctrine\Common\EventArgs;
use Doctrine\ORM\Event\PostLoadEventArgs;

/**
 * @see Events::postLoad
 */
class PostLoad implements Event
{
    use BaseEventArgs;

    /**
     * @param null|PostLoadEventArgs $eventArgs
     */
    public function setEventArgs(?EventArgs $eventArgs): void
    {
        $this->object = $eventArgs->getObject();
    }
}
