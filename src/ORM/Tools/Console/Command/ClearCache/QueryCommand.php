<?php

declare(strict_types=1);

namespace Hyperf\Doctrine\ORM\Tools\Console\Command\ClearCache;

use Doctrine\ORM\Tools\Console\Command\ClearCache\QueryCommand as DoctrineQueryCommand;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Doctrine\EntityManagerFactory;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class QueryCommand extends HyperfCommand
{
    public function configure(): void
    {
        parent::configure();
        $this->setName('doctrine:clear-cache:query')
            ->setDescription('Clear all query cache of the various cache drivers')
            ->addOption('em', null, InputOption::VALUE_REQUIRED, 'Name of the entity manager to operate on')
            ->setHelp('The <info>%command.name%</info> command is meant to clear the query cache of associated Entity Manager.');
    }

    public function handle(): void
    {
        $command = new DoctrineQueryCommand(
            new SingleManagerProvider(EntityManagerFactory::getManager())
        );
        $command->execute($this->input, $this->output);
    }
}
