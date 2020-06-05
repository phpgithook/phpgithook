<?php

declare(strict_types=1);

namespace PHPGithook\Runner\Tests;

use League\Flysystem\Filesystem;
use League\Flysystem\Memory\MemoryAdapter;

trait FilesystemTrait
{
    private static ?Filesystem $fs;

    protected function getFilesystem(): Filesystem
    {
        if (!self::$fs) {
            $adapter = new MemoryAdapter();
            self::$fs = new Filesystem($adapter);
        }

        return self::$fs;
    }
}
