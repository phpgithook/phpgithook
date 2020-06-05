<?php

declare(strict_types=1);

namespace PHPGithook\Runner\Command\Hooks;

use Generator;
use PHPGithook\Runner\Utils\Configuration;
use PHPGithook\Runner\Utils\ModuleFinder;

trait HookRunnerTrait
{
    /**
     * @return Generator<HookClass>
     */
    protected function getHookClasses(
        ModuleFinder $finder,
        Configuration $configuration,
        string $interfaceName
    ): Generator {
        $installed = $finder->findInstalledModules();
        foreach ($installed as $item) {
            $config = $configuration->getModuleConfiguration($item);
            if ($item instanceof $interfaceName) {
                yield new HookClass($config, $item);
            }

            foreach ($item->classes() as $class) {
                if (class_exists($class)) {
                    $obj = new $class();
                    if ($obj instanceof $interfaceName) {
                        yield new HookClass($config, $obj);
                    }
                }
            }
        }
    }
}
