<?php

declare(strict_types=1);

namespace Hyperf\Doctrine\Event;

use Doctrine\Common\EventArgs;

interface Event
{
    public function setEventArgs(?EventArgs $eventArgs): void;
}
