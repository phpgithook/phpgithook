<?php

declare(strict_types=1);

namespace PHPGithook\Runner\Command\Hooks;

use PHPGithook\Runner\Command\AbstractCommand;

abstract class AbstractHookCommand extends AbstractCommand
{
    use HookRunnerTrait;

    public function isHidden(): bool
    {
        return true;
    }
}
