<?php

declare(strict_types=1);

namespace PHPGithook\Runner\Tests\Utils;

use League\Flysystem\Filesystem;
use League\Flysystem\Memory\MemoryAdapter;
use PHPGithook\ModuleInterface\PHPGithookSetupInterface;
use PHPGithook\Runner\Utils\Configuration;
use PHPGithook\Runner\Utils\GitFilesystem;
use PHPGithook\Runner\Utils\ModuleFinder;
use PHPUnit\Framework\TestCase;

class ModuleFinderTest extends TestCase
{
    private ModuleFinder $finder;
    private Configuration $config;

    protected function setUp(): void
    {
        parent::setUp();
        $memory = new MemoryAdapter();
        $fs = new Filesystem($memory);
        $fs->createDir('.git/hooks');
        $fs->createDir('vendor');
        $fs->createDir('Modules');
        $fs->write('Modules/Setup.php', file_get_contents(__DIR__.'/../TestModule/Setup.php'));

        $directory = new GitFilesystem('', $fs, ['Modules']);
        $this->config = new Configuration($directory);
        $this->config->createConfiguration();
        $this->finder = new ModuleFinder($directory, $this->config);
    }

    /**
     * @test
     */
    public function can_find_avaiable_modules(): void
    {
        $this->assertCount(1, $this->finder->findAvailableModules());
        $this->assertCount(0, $this->finder->findInstalledModules());
    }

    /**
     * @test
     */
    public function can_find_installed_modules(): void
    {
        $modules = $this->finder->findAvailableModules();
        $moduleKey = array_key_first($modules);
        $this->config->installModule($modules[$moduleKey]);
        $this->assertCount(0, $this->finder->findAvailableModules());
        $this->assertCount(1, $this->finder->findInstalledModules());
    }

    /**
     * @test
     */
    public function is_module_installed(): void
    {
        $modules = $this->finder->findAvailableModules();
        $moduleKey = array_key_first($modules);

        $this->assertNull($this->finder->isModuleInstalled($moduleKey));
        $this->assertInstanceOf(PHPGithookSetupInterface::class, $this->finder->findModule($moduleKey));
        $this->assertSame($moduleKey, $this->finder->getAvailableModuleList());

        $this->config->installModule($modules[$moduleKey]);
        $this->assertNull($this->finder->findModule($moduleKey));
        $this->assertInstanceOf(PHPGithookSetupInterface::class, $this->finder->isModuleInstalled($moduleKey));
        $this->assertSame($moduleKey, $this->finder->getInstalledModulesList());
    }
}
