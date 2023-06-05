<?php

declare(strict_types=1);

namespace Hyperf\Doctrine\Event;

/**
 * @internal
 */
trait BaseEventArgs
{
    protected object $object;

    public function getObject(): object
    {
        return $this->object;
    }
}
