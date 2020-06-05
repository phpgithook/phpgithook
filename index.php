<?php

declare(strict_types=1);

use PHPGithook\Runner\Command;
use Symfony\Component\Console\Application;

$autoloadDirs = [
    __DIR__.'/vendor/autoload.php',
    __DIR__.'/../vendor/autoload.php',
    __DIR__.'/../../vendor/autoload.php',
    __DIR__.'/../../../vendor/autoload.php',
];

foreach ($autoloadDirs as $autoloadDir) {
    if (file_exists($autoloadDir)) {
        require $autoloadDir;
        break;
    }
}

$version = 'dev-master';

$app = new Application('PHPGithook - '.Command\AbstractCommand::WEBPAGE, $version);
$app->addCommands([
    new Command\InitCommand(),
    new Command\UninstallCommand(),
    new Command\Module\ListCommand(),
    new Command\Module\InstallCommand(),
    new Command\Module\UninstallCommand(),
    new Command\Module\ReconfigureCommand(),
    new Command\Hooks\CommitMsgCommand(),
    new Command\Hooks\PostCommitCommand(),
    new Command\Hooks\PrepareCommitCommand(),
    new Command\Hooks\PrePushCommand(),
]);
$app->run();
