<?php

declare(strict_types=1);

namespace PHPGithook\Runner\Utils;

use PHPGithook\ModuleInterface\ConfigurationBag;
use PHPGithook\ModuleInterface\PHPGithookSetupInterface;
use Symfony\Component\Yaml\Yaml;

class Configuration
{
    private const CONFIG_NAMESPACE = 'phpgithook';

    private const CONFIGFILE = '.phpgithook.yaml';

    private GitFilesystem $directory;

    public function __construct(GitFilesystem $directory)
    {
        $this->directory = $directory;
    }

    public function isConfigured(): bool
    {
        return $this->directory->getFilesystem()->has($this->getConfigurationFilepath());
    }

    public function createConfiguration(): void
    {
        $config = ['enabled' => []];
        $this->writeConfiguration($config);
    }

    public function getEnabledModules(): array
    {
        return $this->getConfiguration()['enabled'] ?? [];
    }

    public function installModule(PHPGithookSetupInterface $module): void
    {
        $config = $this->getConfiguration();
        $config['enabled'][$module->getModuleName()]['config'] = [];
        $config['enabled'][$module->getModuleName()]['class']['setup'] = get_class($module);
        $config['enabled'][$module->getModuleName()]['class']['classes'] = $module->classes();
        $this->writeConfiguration($config);
    }

    public function uninstallModule(PHPGithookSetupInterface $module): void
    {
        $config = $this->getConfiguration();
        unset($config['enabled'][$module->getModuleName()]);
        $this->writeConfiguration($config);
    }

    public function addModuleConfiguration(PHPGithookSetupInterface $module, ConfigurationBag $moduleConfig): void
    {
        $config = $this->getConfiguration();
        $config['enabled'][$module->getModuleName()]['config'] = $moduleConfig->all();
        $this->writeConfiguration($config);
    }

    public function deleteConfiguration(): void
    {
        $this->directory->getFilesystem()->delete($this->getConfigurationFilepath());
    }

    public function getModuleConfiguration(PHPGithookSetupInterface $module): ConfigurationBag
    {
        $config = $this->getConfiguration()['enabled'][$module->getModuleName()]['config'] ?? [];

        return new ConfigurationBag($config);
    }

    public function getConfiguration(): array
    {
        return Yaml::parse($this->getConfigurationData())[self::CONFIG_NAMESPACE];
    }

    private function writeConfiguration(array $config): void
    {
        $this->directory->getFilesystem()->put(
            $this->getConfigurationFilepath(),
            Yaml::dump([self::CONFIG_NAMESPACE => $config], 999)
        );
    }

    private function getConfigurationData(): string
    {
        return (string) $this->directory->getFilesystem()->read($this->getConfigurationFilepath());
    }

    private function getConfigurationFilepath(): string
    {
        return sprintf('%s/%s', $this->directory->getHooksDirectory(), self::CONFIGFILE);
    }
}
