<?php

declare(strict_types=1);

namespace Hyperf\Doctrine\ORM\Tools\Console\Command\ClearCache;

use Doctrine\ORM\Tools\Console\Command\ClearCache\MetadataCommand as DoctrineMetadataCommand;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Doctrine\EntityManagerFactory;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class MetadataCommand extends HyperfCommand
{
    public function configure(): void
    {
        parent::configure();
        $this->setName('doctrine:clear-cache:metadata')
            ->setDescription('Clear all metadata cache of the various cache drivers')
            ->addOption('em', null, InputOption::VALUE_REQUIRED, 'Name of the entity manager to operate on')
            ->addOption('flush', null, InputOption::VALUE_NONE, 'If defined, cache entries will be flushed instead of deleted/invalidated.')
            ->setHelp(
                <<<'EOT'
The <info>%command.name%</info> command is meant to clear the metadata cache of associated Entity Manager.
EOT
            );
    }

    public function handle(): void
    {
        $command = new DoctrineMetadataCommand(
            new SingleManagerProvider(EntityManagerFactory::getManager())
        );
        $command->execute($this->input, $this->output);
    }
}
