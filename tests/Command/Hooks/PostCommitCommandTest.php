<?php

declare(strict_types=1);

namespace PHPGithook\Runner\Tests\Command\Hooks;

use PHPGithook\Runner\Command\Hooks\PostCommitCommand;

class PostCommitCommandTest extends AbstractHooks
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setTestmoduleEnabled();
    }

    /**
     * @test
     */
    public function can_run_post_commit(): void
    {
        $this->cmd->execute([]);
        self::assertFileExists(__DIR__.'/../../TestModule/postcommit');
        unlink(__DIR__.'/../../TestModule/postcommit');
    }

    protected function getCommandClass(): string
    {
        return PostCommitCommand::class;
    }
}
