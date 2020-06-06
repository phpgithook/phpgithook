<?php

declare(strict_types=1);

namespace PHPGithook\Runner\Utils;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use RuntimeException;
use Symfony\Component\Console\Style\StyleInterface;
use Twistor\FlysystemStreamWrapper;

class GitFilesystem
{
    private Filesystem $filesystem;
    private array $moduleDirectories;

    public function __construct(string $directory, ?Filesystem $filesystem, array $moduleDirectories = [])
    {
        if (null === $filesystem) {
            $adapter = new Local($directory);
            $this->filesystem = new Filesystem($adapter);
        } else {
            $this->filesystem = $filesystem;
        }

        FlysystemStreamWrapper::register('fly', $this->filesystem);
        $this->moduleDirectories = $moduleDirectories;
    }

    public function getFullPath(): string
    {
        $adapter = $this->filesystem->getAdapter();
        if (method_exists($adapter, 'getPathPrefix')) {
            return $adapter->getPathPrefix();
        }

        return '';
    }

    public function getGitDirectory(): string
    {
        return $this->filesystem->get('.git')->getPath();
    }

    public function getVendorDirectory(): string
    {
        return $this->filesystem->get('vendor')->getPath();
    }

    public function getHooksDirectory(): string
    {
        return $this->filesystem->get('.git/hooks')->getPath();
    }

    public function getFilesystem(): Filesystem
    {
        return $this->filesystem;
    }

    /**
     * @return array<string>
     */
    public function getModulesDirectory(): array
    {
        $dirs = $this->moduleDirectories;
        $dirs[] = $this->getVendorDirectory();

        return $dirs;
    }

    public function getStreamWrappers(): array
    {
        $wrappers = [];
        foreach ($this->getModulesDirectory() as $moduleDir) {
            $wrappers[] = 'fly://'.$moduleDir;
        }

        return $wrappers;
    }

    public function deleteFile(string $path): bool
    {
        if (!$this->filesystem->has($path)) {
            return false;
        }

        return $this->filesystem->delete($path);
    }

    public function createFile(string $path, string $content, bool $executeable = false): bool
    {
        if (!$this->filesystem->has($path)) {
            $write = $this->filesystem->write($path, $content);
            if ($executeable && $this->getFullPath()) {
                chmod($this->getFullPath().'/'.$path, 0777);
            }

            return $write;
        }

        return false;
    }

    public function hasFile(string $path): bool
    {
        return $this->filesystem->has($path);
    }

    public function setWorkingDirectory(string $directory, StyleInterface $io): void
    {
        if (!$this->gitDirectoryExists()) {
            $question = sprintf(
                "Git is not found in the directory '%s'\nPlease specify working directory",
                $directory
            );

            $validator = function (string $answer) {
                $adapter = new Local($answer);
                $this->filesystem = new Filesystem($adapter);
                if (!$this->gitDirectoryExists()) {
                    throw new RuntimeException(sprintf('Git is not found in the directory \'%s/%s\'', $this->getFullPath(), $answer));
                }

                return $answer;
            };

            if (
                $io->ask($question, null, $validator)
                && !$this->filesystem->has('./.git/hooks')
            ) {
                $this->filesystem->createDir('./.git/hooks');
            }
        }

        // Ensure hooks also exists
        if (!$this->filesystem->has('./.git/hooks')) {
            $this->filesystem->createDir('./.git/hooks');
        }
    }

    private function gitDirectoryExists(): bool
    {
        return $this->filesystem->has('.git');
    }
}
