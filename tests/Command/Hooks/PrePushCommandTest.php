<?php

declare(strict_types=1);

namespace PHPGithook\Runner\Tests\Command\Hooks;

use PHPGithook\Runner\Command\Hooks\PrePushCommand;
use stdClass;

class PrePushCommandTest extends AbstractHooks
{
    /**
     * @test
     */
    public function can_run_post_commit(): void
    {
        $this->cmd->execute([
            'destinationName' => 'name',
            'destinationLocation' => 'location',
        ]);
        self::assertFileExists(__DIR__.'/../../TestModule/prepush');
        self::assertSame('name - location', file_get_contents(__DIR__.'/../../TestModule/prepush'));
        unlink(__DIR__.'/../../TestModule/prepush');
    }

    /**
     * @test
     */
    public function can_not_run_if_name_is_not_a_string(): void
    {
        $this->cmd->execute([
            'destinationName' => new stdClass(),
            'destinationLocation' => 'location',
        ]);
        $output = $this->cmd->getDisplay();
        $this->assertStringContainsString('argument destinationName should be a string', $output);
    }

    /**
     * @test
     */
    public function can_not_run_if_location_is_not_a_string(): void
    {
        $this->cmd->execute([
            'destinationName' => 'name',
            'destinationLocation' => new stdClass(),
        ]);
        $output = $this->cmd->getDisplay();
        $this->assertStringContainsString('argument destinationLocation should be a string', $output);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->setTestmoduleEnabled();
    }

    protected function getCommandClass(): string
    {
        return PrePushCommand::class;
    }
}
