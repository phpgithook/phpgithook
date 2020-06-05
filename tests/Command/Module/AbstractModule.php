<?php

declare(strict_types=1);

namespace PHPGithook\Runner\Tests\Command\Module;

use League\Flysystem\Filesystem;
use League\Flysystem\Memory\MemoryAdapter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

abstract class AbstractModule extends TestCase
{
    protected CommandTester $cmd;
    protected Filesystem $fs;
    protected $command;

    abstract protected function getCommandClass(): string;

    protected function setUp(): void
    {
        parent::setUp();
        $memory = new MemoryAdapter();
        $this->fs = new Filesystem($memory);
        $this->fs->createDir('.git/hooks');
        $this->fs->createDir('vendor');
        $this->fs->createDir('Modules');
        $this->fs->write('Modules/Setup.php', file_get_contents(__DIR__.'/../../TestModule/Setup.php'));

        $class = $this->getCommandClass();
        $this->command = new $class($this->fs, ['Modules']);
        $this->cmd = new CommandTester($this->command);
    }

    protected function setNoneenabled(): void
    {
        $this->fs->put('.git/hooks/.phpgithook.yaml', file_get_contents(__DIR__.'/../../resources/.none-enabled.yaml'));
    }

    protected function setTestmoduleEnabled(): void
    {
        $this->fs->put(
            '.git/hooks/.phpgithook.yaml',
            file_get_contents(__DIR__.'/../../resources/.testmodule-enabled.yaml')
        );
    }
}
