<?php

declare(strict_types=1);

namespace PHPGithook\Runner\Command;

use League\Flysystem\Filesystem;
use PHPGithook\Runner\Exception\PhpgithookException;
use PHPGithook\Runner\Utils\Configuration;
use PHPGithook\Runner\Utils\GitFilesystem;
use PHPGithook\Runner\Utils\ModuleFinder;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class AbstractCommand extends Command
{
    public const WEBPAGE = 'https://phpgithook.com';

    protected const NAMESPACE = 'phpgithook';

    protected StyleInterface $io;

    protected Configuration $configuration;

    protected GitFilesystem $directory;

    protected ?Filesystem $filesystem;

    private array $moduleDirectories;

    public function __construct(?Filesystem $filesystem = null, array $moduleDirectories = [])
    {
        parent::__construct();
        $this->filesystem = $filesystem;
        $this->moduleDirectories = $moduleDirectories;
    }

    protected static function isPhpgithookInitialized(Configuration $configuration): bool
    {
        return $configuration->isConfigured();
    }

    protected function configure(): void
    {
        $this
            ->setName(sprintf('%s:%s', self::NAMESPACE, $this->getCommandName()))
            ->setDescription($this->getCommandDescription())
            ->setDefinition($this->getCommandDefinition())
            ->addArgument(
                'directory',
                InputArgument::OPTIONAL,
                'Directory of your project - Should be initialized with `git init` before, running this',
                $this->getRealpath()
            );
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $directoryArgument = $input->getArgument('directory');
        if (!is_string($directoryArgument)) {
            throw new RuntimeException('Directory should be a string');
        }

        $this->io = new SymfonyStyle($input, $output);
        $this->setWorkingDirectory($directoryArgument);
        $this->configuration = new Configuration($this->directory);
        $this->setPhpgithook();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $finder = new ModuleFinder($this->directory, $this->configuration);
        try {
            $this->executeCommand($input, $output, $finder);

            return 0;
        } catch (PhpgithookException $exception) {
            $this->io->error($exception->getMessage());

            return $exception->getCode();
        }
    }

    /**
     * The name of the command.
     */
    abstract protected function getCommandName(): string;

    /**
     * The description of the command.
     */
    abstract protected function getCommandDescription(): string;

    /**
     * Configure the command.
     */
    abstract protected function getCommandDefinition(): InputDefinition;

    /**
     * Execute the command.
     *
     * @throws PhpgithookException
     */
    abstract protected function executeCommand(
        InputInterface $input,
        OutputInterface $output,
        ModuleFinder $finder
    ): void;

    protected function setWorkingDirectory(string $directory): void
    {
        $this->directory = new GitFilesystem(
            $directory,
            $this->filesystem,
            $this->moduleDirectories
        );
        $this->configuration = new Configuration($this->directory);
        $this->directory->setWorkingDirectory($directory, $this->io);
    }

    protected function setPhpgithook(): void
    {
        // phpgithook is not initialized in the current git dir
        if (!self::isPhpgithookInitialized($this->configuration)) {
            throw new RuntimeException(sprintf("phpgithook is not initialized in the current working directory\nYou can enable it with the command\n%s:init", self::NAMESPACE));
        }
    }

    private function getRealpath(): string
    {
        // In vendor
        if (is_dir(__DIR__.'/../phpgithook')) {
            return dirname(__DIR__, 4).'';
        }

        // Not in vendor
        return dirname(__DIR__, 2).'';
    }
}
