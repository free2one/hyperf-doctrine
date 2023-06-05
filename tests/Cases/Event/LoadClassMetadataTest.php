<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Event;

use Hyperf\Doctrine\Event\LoadClassMetadata;
use Hyperf\Doctrine\ORM\Tools\Console\Command\ClearCache\MetadataCommand;
use HyperfTest\Mock\Entity\User;
use HyperfTest\Tools\CommandTestHelper;

/**
 * @internal
 * @coversNothing
 */
class LoadClassMetadataTest extends AbstractEventTestCase
{
    public function getTestEvent(): string
    {
        return LoadClassMetadata::class;
    }

    /**
     * @param LoadClassMetadata $event
     */
    public function process(object $event): void
    {
        parent::process($event);
        $this->assertNotEmpty($event->getClassMetadata());
    }

    public function test()
    {
        CommandTestHelper::execCmdAndCheck('doctrine:clear-cache:metadata', new MetadataCommand());
        $metaDataFactory = $this->getManager()->getMetadataFactory();
        $metaDataFactory->getMetadataFor(User::class);
    }
}
