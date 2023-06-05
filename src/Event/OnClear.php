<?php

declare(strict_types=1);

namespace Hyperf\Doctrine\Event;

use Doctrine\Common\EventArgs;
use Doctrine\ORM\Event\OnClearEventArgs;

/**
 * @see Events::onClear
 */
class OnClear implements Event
{
    /**
     * @param null|OnClearEventArgs $eventArgs
     */
    public function setEventArgs(?EventArgs $eventArgs): void
    {
    }
}
