<?php

declare(strict_types=1);

namespace PHPGithook\Runner\Tests\Command\Module;

use PHPGithook\Runner\Command\Module\UninstallCommand;

class UninstallCommandTest extends AbstractModule
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setNoneenabled();
    }

    /**
     * @test
     */
    public function can_not_uninstall_module_that_is_not_installed(): void
    {
        $this->cmd->execute([
            'module' => 'test-module',
        ]);
        $output = $this->cmd->getDisplay();
        self::assertStringContainsString("Module 'test-module' not found", $output);
    }

    /**
     * @test
     */
    public function can_uninstall_module(): void
    {
        $this->setTestmoduleEnabled();
        $this->cmd->execute([
            'module' => 'test-module',
        ]);
        $output = $this->cmd->getDisplay();
        self::assertStringContainsString("Module 'test-module' has been uninstalled", $output);
        self::assertStringNotContainsString('test-module', $this->fs->read('.git/hooks/.phpgithook.yaml'));
    }

    protected function getCommandClass(): string
    {
        return UninstallCommand::class;
    }
}
