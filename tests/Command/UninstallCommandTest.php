<?php

declare(strict_types=1);

namespace PHPGithook\Runner\Tests\Command;

use League\Flysystem\Filesystem;
use League\Flysystem\Memory\MemoryAdapter;
use PHPGithook\Runner\Command\UninstallCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Exception\MissingInputException;
use Symfony\Component\Console\Tester\CommandTester;

class UninstallCommandTest extends TestCase
{
    private CommandTester $commandTester;

    private Filesystem $fs;

    protected function setUp(): void
    {
        parent::setUp();
        $adapter = new MemoryAdapter();
        $this->fs = new Filesystem($adapter);
        $this->fs->createDir('.git/hooks');
        $this->fs->write('.git/hooks/.phpgithook.yaml', '');
        $this->fs->write('.git/hooks/commit-msg', '');
        $this->commandTester = new CommandTester(new UninstallCommand($this->fs));
    }

    /**
     * @test
     */
    public function will_not_delete_if_force_is_not_added(): void
    {
        $this->commandTester->execute([]);
        $output = $this->commandTester->getDisplay();
        self::assertStringContainsString('You must run this command', $output);
    }

    /**
     * @test
     */
    public function will_remove_config_but_not_hook(): void
    {
        $this->commandTester->setInputs(['no']);
        $this->commandTester->execute([
            '--force' => true,
        ]);

        $this->assertFalse($this->fs->has('.git/hooks/.phpgithook.yaml'));
        $this->assertTrue($this->fs->has('.git/hooks/commit-msg'));
    }

    /**
     * @test
     */
    public function will_remove_hook(): void
    {
        $this->commandTester->setInputs(['yes']);
        $this->commandTester->execute([
            '--force' => true,
        ]);

        $this->assertFalse($this->fs->has('.git/hooks/.phpgithook.yaml'));
        $this->assertFalse($this->fs->has('.git/hooks/commit-msg'));
    }

    /**
     * @test
     */
    public function can_not_uninstall_if_not_installed(): void
    {
        $this->expectException(MissingInputException::class);

        $this->fs->deleteDir('.git');
        $this->commandTester->execute([
            '--force' => true,
        ]);
    }
}
