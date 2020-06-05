<?php

declare(strict_types=1);

namespace PHPGithook\Runner\Tests\Command\Hooks;

use PHPGithook\Runner\Tests\Command\Module\AbstractModule;

abstract class AbstractHooks extends AbstractModule
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertTrue($this->command->isHidden());
    }

    protected function getMessageFile(): string
    {
        $dir = __DIR__.'/message';
        if (!file_exists($dir)) {
            file_put_contents($dir, 'commit message');
        }

        return $dir;
    }

    protected function tearDown(): void
    {
        unlink($this->getMessageFile());
    }
}
