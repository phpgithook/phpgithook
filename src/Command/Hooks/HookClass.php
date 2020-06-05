<?php

declare(strict_types=1);

namespace PHPGithook\Runner\Command\Hooks;

use PHPGithook\ModuleInterface\ConfigurationBag;
use PHPGithook\ModuleInterface\PHPGithookCommitMsgInterface;
use PHPGithook\ModuleInterface\PHPGithookPostCommitInterface;
use PHPGithook\ModuleInterface\PHPGithookPreCommitInterface;
use PHPGithook\ModuleInterface\PHPGithookPrepareCommitMsgInterface;
use PHPGithook\ModuleInterface\PHPGithookPrepushInterface;

class HookClass
{
    public ConfigurationBag $configuration;

    /**
     * @var object|PHPGithookCommitMsgInterface|PHPGithookPostCommitInterface|PHPGithookPreCommitInterface|PHPGithookPrepareCommitMsgInterface|PHPGithookPrepushInterface
     */
    public object $object;

    public function __construct(ConfigurationBag $configuration, object $object)
    {
        $this->configuration = $configuration;
        $this->object = $object;
    }
}
