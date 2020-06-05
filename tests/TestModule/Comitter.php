<?php

declare(strict_types=1);

namespace PHPGithook\Runner\Tests\TestModule;

use PHPGithook\ModuleInterface\ConfigurationBag;
use PHPGithook\ModuleInterface\PHPGithookPostCommitInterface;
use PHPGithook\ModuleInterface\PHPGithookPrepushInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Comitter implements PHPGithookPostCommitInterface, PHPGithookPrepushInterface
{
    public function postCommit(InputInterface $input, OutputInterface $output, ConfigurationBag $configuration): void
    {
        file_put_contents(__DIR__.'/postcommit', '');
    }

    public function prePush(
        string $destinationName,
        string $destinationLocation,
        InputInterface $input,
        OutputInterface $output,
        ConfigurationBag $configuration
    ): bool {
        file_put_contents(__DIR__.'/prepush', $destinationName.' - '.$destinationLocation);

        return true;
    }
}
