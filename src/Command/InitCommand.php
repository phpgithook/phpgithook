<?php

declare(strict_types=1);

namespace PHPGithook\Runner\Command;

use PHPGithook\Runner\Utils\Configuration;
use PHPGithook\Runner\Utils\ModuleFinder;
use RuntimeException;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class InitCommand extends AbstractCommand
{
    public const TEMPLATES = [
        __DIR__.'/../../templates/commit-msg.php' => 'commit-msg',
        __DIR__.'/../../templates/post-commit.php' => 'post-commit',
        __DIR__.'/../../templates/pre-push.php' => 'pre-push',
        __DIR__.'/../../templates/prepare-commit.php' => 'prepare-commit',
    ];

    protected function getCommandName(): string
    {
        return 'init';
    }

    protected function getCommandDescription(): string
    {
        return 'Initialize phpgithook';
    }

    protected function getCommandDefinition(): InputDefinition
    {
        return new InputDefinition(
            [
                new InputOption('phpbin', null, InputOption::VALUE_OPTIONAL, 'PHP executable', '/usr/bin/php'),
                new InputOption(
                    'phpgithookbin', null, InputOption::VALUE_OPTIONAL, 'PHPGithook executable',
                    dirname(__DIR__, 2).'/bin/phpgithook'
                ),
            ]
        );
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $directoryArgument = $input->getArgument('directory');
        if (!is_string($directoryArgument)) {
            throw new RuntimeException('Directory should be a string');
        }

        $this->io = new SymfonyStyle($input, $output);
        $this->setWorkingDirectory($directoryArgument);
        $this->configuration = new Configuration($this->directory);
    }

    protected function executeCommand(
        InputInterface $input,
        OutputInterface $output,
        ModuleFinder $finder
    ): void {
        $phpbinArgument = $input->getOption('phpbin');
        if (!is_string($phpbinArgument)) {
            throw new RuntimeException('phpbin should be a string');
        }

        $phpgithookArgument = $input->getOption('phpgithookbin');
        if (!is_string($phpgithookArgument)) {
            throw new RuntimeException('phpgithookbin should be a string');
        }

        if ($this->configuration->isConfigured()) {
            if ($this->io->confirm(
                "PHPGithook is already initialized in {$this->directory->getFullPath()}{$this->directory->getGitDirectory()}.\nDo you want to reset everything to default?",
                false
            )) {
                $this->configuration->deleteConfiguration();
                $this->deleteTemplates();
            } else {
                $this->io->note('PHPGithook has not been reset');

                if ($this->io->confirm('Do you want to reset the all/some of the hooks?')) {
                    $this->createTemplates($phpbinArgument, $phpgithookArgument);

                    return;
                }

                return;
            }
        }

        $this->configuration->createConfiguration();
        $this->createTemplates($phpbinArgument, $phpgithookArgument);
    }

    private function deleteTemplates(): void
    {
        foreach (self::TEMPLATES as $destinationFile) {
            $this->deleteFile($destinationFile);
        }
    }

    private function createTemplates(string $phpbin, string $phpgithookbin): void
    {
        foreach (self::TEMPLATES as $template => $destinationFile) {
            $this->createFile(
                $phpbin,
                $phpgithookbin,
                $template,
                $destinationFile
            );
        }
    }

    private function deleteFile(string $destinationFile): void
    {
        $hooksDir = $this->directory->getHooksDirectory();
        if ($this->directory->deleteFile("{$hooksDir}/{$destinationFile}")) {
            $this->io->warning("{$destinationFile} deleted");
        }
    }

    private function createFile(
        string $phpbin,
        string $phpgithookbin,
        string $templateFile,
        string $destinationFile
    ): void {
        $createContent = function (string $template, array $parameters) {
            ob_start();
            extract($parameters, EXTR_SKIP);
            /** @noinspection PhpIncludeInspection */
            include $template;

            return ob_get_clean();
        };

        $hooksDir = $this->directory->getHooksDirectory();
        $destination = "{$hooksDir}/{$destinationFile}";
        $fileContent = $createContent($templateFile, ['phpbin' => $phpbin, 'phpgithookbin' => $phpgithookbin]);
        if ($this->directory->createFile($destination, $fileContent)) {
            return;
        }

        if ($this->io->confirm("File '{$destinationFile}' already exists - Do you want me to overwrite it?", false)) {
            $this->directory->deleteFile($destinationFile);
            $this->directory->createFile($destinationFile, $fileContent);
            $this->io->note("{$destinationFile} has been overwritten");

            return;
        }
    }
}
