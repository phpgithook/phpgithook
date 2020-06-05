<?php

declare(strict_types=1);

namespace PHPGithook\Runner\Tests\Command\Module;

use PHPGithook\Runner\Command\Module\InstallCommand;

class InstallCommandTest extends AbstractModule
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setNoneenabled();
    }

    /**
     * @test
     */
    public function can_install_module(): void
    {
        $this->cmd->execute([
            'module' => 'test-module',
        ]);

        self::assertStringContainsString('test-module', $this->fs->read('.git/hooks/.phpgithook.yaml'));
        self::assertStringContainsString('true', $this->fs->read('.git/hooks/.phpgithook.yaml'));
    }

    /**
     * @test
     */
    public function can_not_install_module_that_does_not_exists(): void
    {
        $this->cmd->execute([
            'module' => 'foo',
        ]);
        $output = $this->cmd->getDisplay();
        self::assertStringContainsString("Module 'foo' does not exists", $output);
    }

    /**
     * @test
     */
    public function can_not_install_already_installed_module(): void
    {
        $this->cmd->execute([
            'module' => 'test-module',
        ]);

        $this->cmd->execute([
            'module' => 'test-module',
        ]);
        $output = $this->cmd->getDisplay();
        self::assertStringContainsString("Module 'test-module' is already installed", $output);
    }

    protected function getCommandClass(): string
    {
        return InstallCommand::class;
    }
}
