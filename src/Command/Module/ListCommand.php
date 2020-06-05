<?php

declare(strict_types=1);

namespace PHPGithook\Runner\Command\Module;

use PHPGithook\ModuleInterface\PHPGithookSetupInterface;
use PHPGithook\Runner\Command\AbstractCommand;
use PHPGithook\Runner\Utils\ModuleFinder;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ListCommand extends AbstractCommand
{
    protected function getCommandName(): string
    {
        return 'module:list';
    }

    protected function getCommandDescription(): string
    {
        return 'List available and installed modules';
    }

    protected function getCommandDefinition(): InputDefinition
    {
        return new InputDefinition(
            [
                new InputOption('only-installed', null, InputOption::VALUE_NONE, 'Only show installed modules'),
                new InputOption('only-available', null, InputOption::VALUE_NONE, 'Only show available modules'),
            ]
        );
    }

    protected function executeCommand(
        InputInterface $input,
        OutputInterface $output,
        ModuleFinder $finder
    ): void {
        $available = [];
        $installed = [];
        if (!$input->getOption('only-installed')) {
            $available = $finder->findAvailableModules();
        }

        if (!$input->getOption('only-available')) {
            $installed = $finder->findInstalledModules();
        }

        $info = function (PHPGithookSetupInterface $info, bool $installed) {
            return [
                $info->getVisualName(),
                $info->getVersion(),
                $info->getDescription(),
                $installed ? 'X' : '',
                sprintf('%s:module:%s %s', self::NAMESPACE, $installed ? 'unintall' : 'install', $info->getModuleName()),
            ];
        };

        $modules = [];
        foreach ($installed as $item) {
            $modules[] = $info($item, true);
        }

        foreach ($available as $item) {
            $modules[] = $info($item, false);
        }

        if (!$modules) {
            $this->io->text(
                [
                    'No modules are available, or installed',
                    'You can find official modules on the webpage',
                    self::WEBPAGE,
                ]
            );

            return;
        }

        $this->io->title('PHPGithook - modules');
        $this->io->table(['Name', 'Version', 'Description', 'Installed', ''], $modules);
    }
}
