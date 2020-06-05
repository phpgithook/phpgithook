<?php

declare(strict_types=1);

namespace PHPGithook\Runner\Command\Hooks;

use PHPGithook\ModuleInterface\PHPGithookPostCommitInterface;
use PHPGithook\Runner\Utils\ModuleFinder;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PostCommitCommand extends AbstractHookCommand
{
    protected function getCommandName(): string
    {
        return 'hook:post-commit';
    }

    protected function getCommandDescription(): string
    {
        return 'Run "post-commit" command';
    }

    protected function getCommandDefinition(): InputDefinition
    {
        return new InputDefinition();
    }

    protected function executeCommand(InputInterface $input, OutputInterface $output, ModuleFinder $finder): void
    {
        foreach ($this->getHookClasses($finder, $this->configuration, PHPGithookPostCommitInterface::class) as $hook) {
            if (method_exists($hook->object, 'postCommit')) {
                $hook->object->postCommit(
                    $input,
                    $output,
                    $hook->configuration
                );
            }
        }
    }
}
