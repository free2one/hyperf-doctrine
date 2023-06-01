<?php

declare(strict_types=1);

namespace Hyperf\Doctrine\ORM\Tools\Console\Command\ClearCache;

use Doctrine\ORM\Tools\Console\Command\ClearCache\ResultCommand as DoctrineResultCommand;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Doctrine\EntityManagerFactory;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class ResultCommand extends HyperfCommand
{
    public function configure(): void
    {
        parent::configure();
        $this->setName('doctrine:clear-cache:result')
            ->setDescription('Clear all result cache of the various cache drivers')
            ->addOption('em', null, InputOption::VALUE_REQUIRED, 'Name of the entity manager to operate on')
            ->addOption('flush', null, InputOption::VALUE_NONE, 'If defined, cache entries will be flushed instead of deleted/invalidated.')
            ->setHelp(<<<'EOT'
The <info>%command.name%</info> command is meant to clear the result cache of associated Entity Manager.
It is possible to invalidate all cache entries at once - called delete -, or flushes the cache provider
instance completely.

The execution type differ on how you execute the command.
If you want to invalidate the entries (and not delete from cache instance), this command would do the work:

<info>%command.name%</info>

Alternatively, if you want to flush the cache provider using this command:

<info>%command.name% --flush</info>

Finally, be aware that if <info>--flush</info> option is passed, not all cache providers are able to flush entries,
because of a limitation of its execution nature.
EOT);
    }

    public function handle(): void
    {
        $command = new DoctrineResultCommand(
            new SingleManagerProvider(EntityManagerFactory::getManager())
        );
        $command->execute($this->input, $this->output);
    }
}
