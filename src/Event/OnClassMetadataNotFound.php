<?php

declare(strict_types=1);

namespace Hyperf\Doctrine\Event;

use Doctrine\Common\EventArgs;
use Doctrine\ORM\Event\OnClassMetadataNotFoundEventArgs;

/**
 * @see Events::onClassMetadataNotFound
 */
class OnClassMetadataNotFound implements Event
{
    private string $className;

    /**
     * @param null|OnClassMetadataNotFoundEventArgs $eventArgs
     */
    public function setEventArgs(?EventArgs $eventArgs): void
    {
        $this->className = $eventArgs->getClassName();
    }

    /**
     * Retrieve class name for which a failed metadata fetch attempt was executed.
     */
    public function getClassName(): string
    {
        return $this->className;
    }
}
