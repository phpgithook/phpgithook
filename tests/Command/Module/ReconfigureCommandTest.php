<?php

declare(strict_types=1);

namespace PHPGithook\Runner\Tests\Command\Module;

use PHPGithook\Runner\Command\Module\ReconfigureCommand;

class ReconfigureCommandTest extends AbstractModule
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setTestmoduleEnabled();
    }

    /**
     * @test
     */
    public function can_reconfigure_module(): void
    {
        self::assertStringNotContainsString('true', $this->fs->read('.git/hooks/.phpgithook.yaml'));
        $this->cmd->execute([
            'module' => 'test-module',
        ]);
        $output = $this->cmd->getDisplay();
        self::assertStringContainsString("Module 'Test Module' has been reconfigured", $output);
        self::assertStringContainsString('true', $this->fs->read('.git/hooks/.phpgithook.yaml'));
    }

    /**
     * @test
     */
    public function can_not_reconfigure_module_that_is_not_installed(): void
    {
        $this->cmd->execute([
            'module' => 'foo',
        ]);
        $output = $this->cmd->getDisplay();
        self::assertStringContainsString("Module 'foo' not found", $output);
    }

    protected function getCommandClass(): string
    {
        return ReconfigureCommand::class;
    }
}
