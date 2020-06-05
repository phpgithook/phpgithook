<?php

declare(strict_types=1);

namespace PHPGithook\Runner\Command\Hooks;

use PHPGithook\ModuleInterface\PHPGithookCommitMsgInterface;
use PHPGithook\Runner\Exception\PhpgithookException;
use PHPGithook\Runner\Utils\ModuleFinder;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CommitMsgCommand extends AbstractHookCommand
{
    protected function getCommandName(): string
    {
        return 'hook:commit-msg';
    }

    protected function getCommandDescription(): string
    {
        return 'Run "commit-msg" command';
    }

    protected function getCommandDefinition(): InputDefinition
    {
        return new InputDefinition(
            [
                new InputArgument('messagefile', InputArgument::REQUIRED, 'Commit message file'),
            ]
        );
    }

    protected function executeCommand(InputInterface $input, OutputInterface $output, ModuleFinder $finder): void
    {
        if (!is_string($input->getArgument('messagefile'))) {
            throw new PhpgithookException('argument messagefile should be a string');
        }

        if (!file_exists($input->getArgument('messagefile'))) {
            throw new PhpgithookException("File '{$input->getArgument('messagefile')}' does not exists");
        }

        $message = file_get_contents($input->getArgument('messagefile'));

        foreach ($this->getHookClasses($finder, $this->configuration, PHPGithookCommitMsgInterface::class) as $hook) {
            if (method_exists($hook->object, 'commitMsg')) {
                $hook->object->commitMsg(
                    $message,
                    $input,
                    $output,
                    $hook->configuration
                );
            }
        }

        file_put_contents($input->getArgument('messagefile'), $message);
    }
}
