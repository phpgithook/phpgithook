<?php

declare(strict_types=1);

namespace PHPGithook\Runner\Tests\Command\Module;

use PHPGithook\Runner\Command\Module\ListCommand;

class ListCommandTest extends AbstractModule
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setNoneenabled();
    }

    /**
     * @test
     */
    public function can_list_modules(): void
    {
        $this->cmd->execute([]);
        $output = $this->cmd->getDisplay();
        $this->assertStringContainsString('Test Module', $output);
        $this->assertStringNotContainsString('X', $output);
    }

    /**
     * @test
     */
    public function can_only_list_available_modules(): void
    {
        $this->cmd->execute(
            [
                '--only-available' => true,
            ]
        );
        $output = $this->cmd->getDisplay();
        $this->assertStringContainsString('Test Module', $output);
        $this->assertStringNotContainsString('X', $output);
    }

    /**
     * @test
     */
    public function can_only_list_installed_modules(): void
    {
        $this->cmd->execute(
            [
                '--only-installed' => true,
            ]
        );
        $output = $this->cmd->getDisplay();
        $this->assertStringNotContainsString('Test Module', $output);
    }

    /**
     * @test
     */
    public function will_list_installed_modules(): void
    {
        $this->setTestmoduleEnabled();
        $this->cmd->execute(
            [
                '--only-installed' => true,
            ]
        );
        $output = $this->cmd->getDisplay();
        $this->assertStringContainsString('Test Module', $output);
        $this->assertStringContainsString('X', $output);
    }

    protected function getCommandClass(): string
    {
        return ListCommand::class;
    }
}
