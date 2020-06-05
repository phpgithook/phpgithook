<?php

declare(strict_types=1);

namespace PHPGithook\Runner\Tests\TestModule;

use PHPGithook\ModuleInterface\ConfigurationBag;
use PHPGithook\ModuleInterface\PHPGithookCommitMsgInterface;
use PHPGithook\ModuleInterface\PHPGithookPrepareCommitMsgInterface;
use PHPGithook\ModuleInterface\PHPGithookSetupInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Setup implements PHPGithookSetupInterface, PHPGithookPrepareCommitMsgInterface, PHPGithookCommitMsgInterface
{
    public function getVisualName(): string
    {
        return 'Test Module';
    }

    public function getDescription(): string
    {
        return 'Test';
    }

    public function getModuleName(): string
    {
        return 'test-module';
    }

    public function getVersion(): string
    {
        return '1.0';
    }

    public function createConfiguration(
        InputInterface $input,
        OutputInterface $output,
        ConfigurationBag $configuration
    ): void {
        $configuration->set('test', true);
    }

    public function classes(): array
    {
        return [
            Comitter::class,
        ];
    }

    public function prepareCommitMsg(
        string &$commitMessage,
        InputInterface $input,
        OutputInterface $output,
        ConfigurationBag $configuration,
        ?string $type = null,
        ?string $sha = null
    ): bool {
        $commitMessage = 'prepare commitmsg '.$commitMessage;

        return true;
    }

    public function commitMsg(
        string &$commitMessage,
        InputInterface $input,
        OutputInterface $output,
        ConfigurationBag $configuration
    ): bool {
        $commitMessage = 'commitmsg '.$commitMessage;

        return true;
    }
}
