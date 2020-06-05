<?php

declare(strict_types=1);

namespace PHPGithook\Runner\Utils;

use PHPGithook\ModuleInterface\PHPGithookSetupInterface;
use ReflectionClass;
use Symfony\Component\Finder\Finder;

class ModuleFinder
{
    private Finder $finder;
    private GitFilesystem $directory;
    private Configuration $configuration;

    public function __construct(GitFilesystem $directory, Configuration $configuration, ?Finder $finder = null)
    {
        $this->finder = $finder ?? new Finder();
        $this->directory = $directory;
        $this->configuration = $configuration;
    }

    /**
     * @return PHPGithookSetupInterface[]
     */
    public function findAvailableModules(): array
    {
        $modules = [];
        $installed = $this->findInstalledModules();
        $this->finder
            ->files()
            ->followLinks()
            ->name('Setup.php')
            ->contains('PHPGithookSetupInterface');

        foreach ($this->finder->in($this->directory->getStreamWrappers()) as $file) {
            if ($class = ClassNamespaceResolver::getClassFullNameFromFile($file->getPathname())) {
                /** @var class-string<PHPGithookSetupInterface> $refClassName */
                $refClassName = $class;
                $reflection = new ReflectionClass($refClassName);
                if ($reflection->implementsInterface(PHPGithookSetupInterface::class)) {
                    /** @var PHPGithookSetupInterface $obj */
                    $obj = new $class();
                    if (!isset($installed[$obj->getModuleName()])) {
                        $modules[$obj->getModuleName()] = new $class();
                    }
                }
            }
        }

        return $modules;
    }

    public function isModuleInstalled(string $moduleName): ?PHPGithookSetupInterface
    {
        $installed = $this->findInstalledModules();

        return $installed[$moduleName] ?? null;
    }

    /**
     * @return PHPGithookSetupInterface[]
     */
    public function findInstalledModules(): array
    {
        $enabled = $this->configuration->getEnabledModules();
        $modules = [];
        if (is_array($enabled)) {
            foreach ($enabled as $enable) {
                if (class_exists($enable['class']['setup'])) {
                    /** @var PHPGithookSetupInterface $obj */
                    $obj = new $enable['class']['setup']();
                    $modules[$obj->getModuleName()] = $obj;
                }
            }
        }

        return $modules;
    }

    public function findModule(string $moduleName): ?PHPGithookSetupInterface
    {
        $available = $this->findAvailableModules();
        foreach ($available as $item) {
            if ($item->getModuleName() === $moduleName) {
                return $item;
            }
        }

        return null;
    }

    public function getInstalledModulesList(): string
    {
        $installed = $this->findInstalledModules();

        return implode(
            "\n",
            array_map(
                function (PHPGithookSetupInterface $module) {
                    return $module->getModuleName();
                },
                $installed
            )
        );
    }

    public function getAvailableModuleList(): string
    {
        $available = $this->findAvailableModules();

        return implode(
            "\n",
            array_map(
                function (PHPGithookSetupInterface $module) {
                    return $module->getModuleName();
                },
                $available
            )
        );
    }
}
