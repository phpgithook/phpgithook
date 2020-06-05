<?php

declare(strict_types=1);

namespace PHPGithook\Runner\Tests\Utils;

use League\Flysystem\Filesystem;
use League\Flysystem\Memory\MemoryAdapter;
use PHPGithook\Runner\Utils\GitFilesystem;
use PHPUnit\Framework\TestCase;

class DirectoryTest extends TestCase
{
    private GitFilesystem $directory;

    /**
     * @test
     */
    public function can_get_git_directory(): void
    {
        self::assertSame('.git', $this->directory->getGitDirectory());
    }

    /**
     * @test
     */
    public function can_get_vendor_directory(): void
    {
        self::assertSame('vendor', $this->directory->getVendorDirectory());
    }

    /**
     * @test
     */
    public function can_get_hooks_directory(): void
    {
        self::assertSame('.git/hooks', $this->directory->getHooksDirectory());
    }

    /**
     * @test
     */
    public function can_get_modules_directory(): void
    {
        $m = $this->directory->getModulesDirectory();
        self::assertCount(2, $m);
        self::assertSame('Modules', $m[0]);
        self::assertSame('vendor', $m[1]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $memory = new MemoryAdapter();
        $fs = new Filesystem($memory);
        $fs->createDir('.git/hooks');
        $fs->createDir('vendor');
        $fs->createDir('Modules');

        $this->directory = new GitFilesystem('', $fs, ['Modules']);
    }
}
