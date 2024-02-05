<?php

declare(strict_types=1);

namespace Hyperf\Doctrine\ORM\Tools\Console\Command\ClearCache;

use Doctrine\ORM\Tools\Console\Command\ClearCache\QueryCommand as DoctrineQueryCommand;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Doctrine\EntityManagerFactory;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
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
            ->addOption('flush', null, InputOption::VALUE_NONE, 'If defined, cache entries will be flushed instead of deleted/invalidated.')
            ->setHelp('The <info>%command.name%</info> command is meant to clear the query cache of associated Entity Manager.');
    }

    public function handle(): int
    {
        $command = new DoctrineQueryCommand(
            new SingleManagerProvider(EntityManagerFactory::getManager())
        );

        $params = [
            'command' => 'orm:clear-cache:query',
        ];
        if ($this->input->getOption('em')) {
            $params['--em'] = $this->input->getOption('em');
        }
        if ($this->input->getOption('flush')) {
            $params['--flush'] = true;
        }

        $input = new ArrayInput($params);
        $app = new Application();
        $app->setAutoExit(false);
        $app->add($command);

        return $app->run($input);
    }
}
