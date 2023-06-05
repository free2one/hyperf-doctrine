<?php

declare(strict_types=1);

namespace Hyperf\Doctrine\Event;

use Doctrine\Common\EventArgs;
use Doctrine\Persistence\Event\PreUpdateEventArgs;
use InvalidArgumentException;

/**
 * @see Events::preUpdate
 */
class PreUpdate implements Event
{
    use BaseEventArgs;

    protected array $entityChangeSet = [];

    /**
     * @param null|PreUpdateEventArgs $eventArgs
     */
    public function setEventArgs(?EventArgs $eventArgs): void
    {
        $this->object = $eventArgs->getObject();
        $this->entityChangeSet = $eventArgs->getEntityChangeSet();
    }

    public function getEntityChangeSet(): array
    {
        return $this->entityChangeSet;
    }

    /**
     * Checks if field has a changeset.
     */
    public function hasChangedField(string $field): bool
    {
        return isset($this->entityChangeSet[$field]);
    }

    /**
     * Gets the old value of the changeset of the changed field.
     */
    public function getOldValue(string $field): mixed
    {
        $this->assertValidField($field);

        return $this->entityChangeSet[$field][0];
    }

    /**
     * Gets the new value of the changeset of the changed field.
     */
    public function getNewValue(string $field): mixed
    {
        $this->assertValidField($field);

        return $this->entityChangeSet[$field][1];
    }

    /**
     * Asserts the field exists in changeset.
     *
     * @throws InvalidArgumentException
     */
    private function assertValidField(string $field): void
    {
        if (! isset($this->entityChangeSet[$field])) {
            throw new InvalidArgumentException(sprintf(
                'Field "%s" is not a valid field of the entity "%s" in PreUpdateEventArgs.',
                $field,
                get_class($this->getObject())
            ));
        }
    }
}
