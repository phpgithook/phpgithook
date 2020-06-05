<?php

declare(strict_types=1);

namespace PHPGithook\Runner\Command\Hooks;

use PHPGithook\ModuleInterface\PHPGithookPrepushInterface;
use PHPGithook\Runner\Exception\PhpgithookException;
use PHPGithook\Runner\Utils\ModuleFinder;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PrePushCommand extends AbstractHookCommand
{
    protected function getCommandName(): string
    {
        return 'hook:pre-push';
    }

    protected function getCommandDescription(): string
    {
        return 'Run "pre-push" command';
    }

    protected function getCommandDefinition(): InputDefinition
    {
        return new InputDefinition(
            [
                new InputArgument('destinationName', InputArgument::REQUIRED, 'Git remote name'),
                new InputArgument('destinationLocation', InputArgument::REQUIRED, 'Git remote uri'),
            ]
        );
    }

    protected function executeCommand(InputInterface $input, OutputInterface $output, ModuleFinder $finder): void
    {
        if (!is_string($input->getArgument('destinationName'))) {
            throw new PhpgithookException('argument destinationName should be a string');
        }

        if (!is_string($input->getArgument('destinationLocation'))) {
            throw new PhpgithookException('argument destinationLocation should be a string');
        }

        foreach ($this->getHookClasses($finder, $this->configuration, PHPGithookPrepushInterface::class) as $hook) {
            if (method_exists($hook->object, 'prePush')) {
                $hook->object->prePush(
                    $input->getArgument('destinationName'),
                    $input->getArgument('destinationLocation'),
                    $input,
                    $output,
                    $hook->configuration
                );
            }
        }
    }
}
