<?php

declare(strict_types=1);

namespace Hyperf\Doctrine\Event;

use Doctrine\Common\EventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\Persistence\Mapping\ClassMetadata;

/**
 * @see Events::loadClassMetadata
 */
class LoadClassMetadata implements Event
{
    private ClassMetadata $classMetadata;

    /**
     * @param null|LoadClassMetadataEventArgs $eventArgs
     */
    public function setEventArgs(?EventArgs $eventArgs): void
    {
        $this->classMetadata = $eventArgs->getClassMetadata();
    }

    public function getClassMetadata(): ClassMetadata
    {
        return $this->classMetadata;
    }
}
