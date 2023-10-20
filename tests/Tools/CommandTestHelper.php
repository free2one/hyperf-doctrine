<?php

declare(strict_types=1);

namespace HyperfTest\Tools;

/*
 * @internal
 */

use Hyperf\Command\Command;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ApplicationInterface;
use PHPUnit\Framework\Assert;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class CommandTestHelper
{
    public static function execCmdAndCheck(string $commandName, Command $command, bool $check = true): void
    {
        $input = new ArrayInput(['command' => $commandName]);
        $output = new NullOutput();
        $application = ApplicationContext::getContainer()->get(ApplicationInterface::class);
        $application->add($command);
        $exitCode = $application->find($commandName)->run($input, $output);
        $check && Assert::assertSame(0, $exitCode);
    }
}
