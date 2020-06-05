<?php

declare(strict_types=1);

namespace PHPGithook\Runner\Command\Module;

use PHPGithook\Runner\Command\AbstractCommand;
use PHPGithook\Runner\Exception\PhpgithookException;
use PHPGithook\Runner\Utils\ModuleFinder;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReconfigureCommand extends AbstractCommand
{
    protected function getCommandName(): string
    {
        return 'module:reconfigure';
    }

    protected function getCommandDescription(): string
    {
        return 'Reconfigure a module';
    }

    protected function getCommandDefinition(): InputDefinition
    {
        return new InputDefinition(
            [
                new InputArgument('module', InputArgument::REQUIRED, 'The module you want to install'),
            ]
        );
    }

    protected function executeCommand(InputInterface $input, OutputInterface $output, ModuleFinder $finder): void
    {
        $moduleArgument = $input->getArgument('module');
        if (!is_string($moduleArgument)) {
            throw new PhpgithookException('argument module needs to be a string');
        }

        if (!$module = $finder->isModuleInstalled($moduleArgument)) {
            throw new PhpgithookException(["Module '{$moduleArgument}' not found", 'Installed modules are', $finder->getInstalledModulesList()]);
        }

        $moduleConfig = $this->configuration->getModuleConfiguration($module);
        $module->createConfiguration($input, $output, $moduleConfig);
        $this->configuration->addModuleConfiguration($module, $moduleConfig);

        $this->io->note("Module '{$module->getVisualName()}' has been reconfigured");
    }
}
