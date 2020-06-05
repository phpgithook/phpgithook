<?php

declare(strict_types=1);

namespace PHPGithook\Runner\Tests\Command;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\Memory\MemoryAdapter;
use PHPGithook\Runner\Command\InitCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class InitCommandTest extends TestCase
{
    private CommandTester $commandTester;

    private Filesystem $fs;

    /**
     * @test
     */
    public function can_initialize(): void
    {
        $this->commandTester->execute([]);
        $output = $this->commandTester->getDisplay();
        self::assertSame('', $output);
        self::assertTrue($this->fs->has('.git/hooks/.phpgithook.yaml'), 'no config');
        self::assertTrue($this->fs->has('.git/hooks/commit-msg'), 'no config');
        self::assertTrue($this->fs->has('.git/hooks/post-commit'), 'no config');
        self::assertTrue($this->fs->has('.git/hooks/pre-push'), 'no config');
        self::assertTrue($this->fs->has('.git/hooks/prepare-commit'), 'no config');
    }

    /**
     * @test
     */
    public function can_re_initialize(): void
    {
        $this->commandTester->execute([]);
        $this->commandTester->setInputs(['yes']);
        $this->commandTester->execute([]);
        $output = $this->commandTester->getDisplay();
        self::assertStringContainsString('[WARNING] commit-msg deleted', $output);
    }

    /**
     * @test
     */
    public function will_not_reinitialize(): void
    {
        $this->commandTester->execute([]);
        $this->commandTester->setInputs(['no']);
        $this->commandTester->execute([]);
        $output = $this->commandTester->getDisplay();
        self::assertStringContainsString('[NOTE] PHPGithook has not been reset', $output);
    }

    /**
     * @test
     */
    public function will_not_overwrite_files(): void
    {
        $this->commandTester->execute([]);
        $this->commandTester->setInputs(['no', 'yes', 'yes', 'no']);
        $this->commandTester->execute([]);
        $output = $this->commandTester->getDisplay();
        self::assertStringContainsString('[NOTE] PHPGithook has not been reset', $output);
        self::assertStringContainsString('has been overwritten', $output);
    }

    /**
     * @test
     */
    public function with_another_git_directory(): void
    {
        $random = random_int(0, 99999);
        $temp = sys_get_temp_dir().'/phpgithooktest/'.$random;

        $adapter = new Local($temp);
        $fs = new Filesystem($adapter);
        $fs->createDir('.git');

        $cmd = new CommandTester(new InitCommand());
        $cmd->execute(
            [
                'directory' => $temp,
            ]
        );
        self::assertTrue($fs->has('.git/hooks/.phpgithook.yaml'));
        self::assertTrue($fs->has('.git/hooks/commit-msg'));

        $fs->deleteDir('.git');
        rmdir($temp);
    }

    /**
     * @test
     */
    public function will_ask_for_directory(): void
    {
        $random = random_int(0, 99999);
        $temp = sys_get_temp_dir().'/phpgithooktest/'.$random;
        mkdir($temp.'/.git', 0777, true);

        $cmd = new CommandTester(new InitCommand());
        $cmd->setInputs([$temp]);
        $cmd->execute(
            [
                'directory' => sys_get_temp_dir(),
            ]
        );
        self::assertFileExists($temp.'/.git/hooks/.phpgithook.yaml');

        $this->rrmdir(dirname($temp));
    }

    protected function setUp(): void
    {
        parent::setUp();
        $adapter = new MemoryAdapter();
        $this->fs = new Filesystem($adapter);
        $this->fs->createDir('.git/hooks');
        $this->fs->createDir('newdir/.git/hooks');
        $this->fs->createDir('vendor');
        $this->fs->createDir('Modules');
        $this->fs->write('Modules/Setup.php', file_get_contents(__DIR__.'/../TestModule/Setup.php'));
        $this->commandTester = new CommandTester(new InitCommand($this->fs));
    }

    private function rrmdir($dir): void
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ('.' === $object || '..' === $object) {
                    continue;
                }
                if (is_dir($dir.DIRECTORY_SEPARATOR.$object) && !is_link($dir.'/'.$object)) {
                    $this->rrmdir($dir.DIRECTORY_SEPARATOR.$object);
                } else {
                    unlink($dir.DIRECTORY_SEPARATOR.$object);
                }
            }
            rmdir($dir);
        }
    }
}
