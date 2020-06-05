<?php

declare(strict_types=1);

namespace PHPGithook\Runner\Command;

use PHPGithook\Runner\Exception\PhpgithookException;
use PHPGithook\Runner\Utils\ModuleFinder;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UninstallCommand extends AbstractCommand
{
    protected function getCommandName(): string
    {
        return 'uninstall';
    }

    protected function getCommandDescription(): string
    {
        return 'Uninstall PHPGithook';
    }

    protected function getCommandDefinition(): InputDefinition
    {
        return new InputDefinition(
            [
                new InputOption('force', null, InputOption::VALUE_NONE, 'You must force to uninstall'),
            ]
        );
    }

    protected function executeCommand(InputInterface $input, OutputInterface $output, ModuleFinder $finder): void
    {
        if (!$input->getOption('force')) {
            throw new PhpgithookException('You must run this command with `--force` to uninstall PHPGithook');
        }

        $this->configuration->deleteConfiguration();
        $hooksDir = $this->directory->getHooksDirectory();
        foreach (InitCommand::TEMPLATES as $file) {
            $filepath = "{$hooksDir}/{$file}";
            if (
                $this->directory->hasFile($filepath)
                && $this->io->confirm("Do you want to delete the file '{$filepath}' ?")
            ) {
                $this->directory->deleteFile($filepath);
            }
        }
    }
}
