<?php

declare(strict_types=1);

namespace PHPGithook\Runner\Command\Hooks;

use PHPGithook\ModuleInterface\PHPGithookPrepareCommitMsgInterface;
use PHPGithook\Runner\Exception\PhpgithookException;
use PHPGithook\Runner\Utils\ModuleFinder;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PrepareCommitCommand extends AbstractHookCommand
{
    protected function getCommandName(): string
    {
        return 'hook:prepare-commit';
    }

    protected function getCommandDescription(): string
    {
        return 'Run "prepare-commit" command';
    }

    protected function getCommandDefinition(): InputDefinition
    {
        return new InputDefinition(
            [
                new InputArgument('messagefile', InputArgument::REQUIRED, 'Commit message'),
                new InputArgument(
                    'type',
                    InputArgument::OPTIONAL,
                    'Type of commit message (message, template, merge, squash, or commit)',
                    ''
                ),
                new InputArgument(
                    'sha',
                    InputArgument::OPTIONAL,
                    'SHA-1 of the commit (only available if working on a existing commit)',
                    ''
                ),
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

        if (!is_string($input->getArgument('type'))) {
            throw new PhpgithookException('argument type should be a string');
        }

        if (!is_string($input->getArgument('sha'))) {
            throw new PhpgithookException('argument sha should be a string');
        }

        $message = file_get_contents($input->getArgument('messagefile'));
        foreach ($this->getHookClasses(
            $finder,
            $this->configuration,
            PHPGithookPrepareCommitMsgInterface::class
        ) as $hook) {
            if (method_exists($hook->object, 'prepareCommitMsg')) {
                $hook->object->prepareCommitMsg(
                    $message,
                    $input,
                    $output,
                    $hook->configuration,
                    $input->getArgument('type'),
                    $input->getArgument('sha')
                );
            }
        }
        file_put_contents($input->getArgument('messagefile'), $message);
    }
}
