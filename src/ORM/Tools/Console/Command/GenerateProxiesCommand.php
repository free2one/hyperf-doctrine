<?php

declare(strict_types=1);

namespace Hyperf\Doctrine\ORM\Tools\Console\Command;

use Doctrine\ORM\Tools\Console\Command\GenerateProxiesCommand as DoctrineGenerateProxiesCommand;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Doctrine\EntityManagerFactory;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class GenerateProxiesCommand extends HyperfCommand
{
    public function configure(): void
    {
        parent::configure();
        $this->setDescription('');
        $this->setName('doctrine:generate-proxies')
            ->setAliases(['doctrine:generate:proxies'])
            ->setDescription('Generates proxy classes for entity classes')
            ->addArgument('dest-path', InputArgument::OPTIONAL, 'The path to generate your proxy classes. If none is provided, it will attempt to grab from configuration.')
            ->addOption('em', null, InputOption::VALUE_REQUIRED, 'Name of the entity manager to operate on')
            ->addOption('filter', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'A string pattern used to match entities that should be processed.')
            ->setHelp('Generates proxy classes for entity classes.');
    }

    public function handle(): void
    {
        $command = new DoctrineGenerateProxiesCommand(
            new SingleManagerProvider(EntityManagerFactory::getManager())
        );
        $command->execute($this->input, $this->output);
    }
}
