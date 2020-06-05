<?php

declare(strict_types=1);

namespace PHPGithook\Runner\Tests\Utils;

use League\Flysystem\Filesystem;
use League\Flysystem\Memory\MemoryAdapter;
use PHPGithook\ModuleInterface\ConfigurationBag;
use PHPGithook\Runner\Tests\TestModule\Setup;
use PHPGithook\Runner\Utils\Configuration;
use PHPGithook\Runner\Utils\GitFilesystem;
use PHPUnit\Framework\TestCase;

class ConfigurationTest extends TestCase
{
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

        $directory = new GitFilesystem('', $fs);
        $this->config = new Configuration($directory);
    }

    /**
     * @test
     */
    public function init_delete_configuration(): void
    {
        $this->config->createConfiguration();
        $this->assertTrue($this->config->isConfigured());
        $this->config->deleteConfiguration();
        self::assertFalse($this->config->isConfigured());
    }

    /**
     * @test
     */
    public function install_uninstall_module(): void
    {
        $this->config->createConfiguration();

        $module = new Setup();

        $this->config->installModule($module);
        $config = $this->config->getConfiguration();
        self::assertCount(1, $this->config->getEnabledModules());
        self::assertArrayHasKey('enabled', $config);
        self::assertArrayHasKey('test-module', $config['enabled']);

        $this->config->addModuleConfiguration($module, new ConfigurationBag(['hello' => 'world']));
        $moduleConfig = $this->config->getModuleConfiguration($module);
        self::assertTrue($moduleConfig->has('hello'));
        self::assertSame('world', $moduleConfig->get('hello'));

        $this->config->uninstallModule($module);
        $config = $this->config->getConfiguration();
        self::assertArrayHasKey('enabled', $config);
        self::assertArrayNotHasKey('test-module', $config['enabled']);
        self::assertCount(0, $this->config->getEnabledModules());
    }
}
