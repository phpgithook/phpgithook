<?php

declare(strict_types=1);

namespace PHPGithook\Runner\Tests\Command\Hooks;

use PHPGithook\Runner\Command\Hooks\CommitMsgCommand;
use stdClass;

class CommitMsgCommandTest extends AbstractHooks
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setTestmoduleEnabled();
    }

    /**
     * @test
     */
    public function will_write_file(): void
    {
        $this->cmd->execute([
            'messagefile' => $this->getMessageFile(),
        ]);
        self::assertStringStartsWith('commitmsg', file_get_contents($this->getMessageFile()));
    }

    /**
     * @test
     */
    public function can_not_run_if_message_is_not_a_string(): void
    {
        $this->cmd->execute([
            'messagefile' => new stdClass(),
        ]);
        $output = $this->cmd->getDisplay();
        self::assertStringContainsString('argument messagefile should be a string', $output);
    }

    /**
     * @test
     */
    public function can_not_run_if_messagefile_does_not_exists(): void
    {
        $this->cmd->execute([
            'messagefile' => 'foo',
        ]);
        $output = $this->cmd->getDisplay();
        self::assertStringContainsString('does not exists', $output);
    }

    protected function getCommandClass(): string
    {
        return CommitMsgCommand::class;
    }
}
