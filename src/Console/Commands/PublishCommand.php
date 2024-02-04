<?php

/**
 * Command class for publishing the necessary configurations for the Matrix library.
 *
 * This command allows users to publish the required configurations for the Matrix library. It copies migration files from the library to the user's project directory, enabling customization and usage.
 *
 * @package Ananiaslitz\Matrix\Console\Commands
 */
namespace Ananiaslitz\Matrix\Console\Commands;

use Hyperf\Command\Command as HyperfCommand;

/**
 * @Command
 */
class PublishCommand extends HyperfCommand
{
    /**
     * The name of the command.
     *
     * @var string|null
     */
    protected ?string $name = 'matrix:publish';

    /**
     * Configure the command.
     */
    public function configure(): void
    {
        parent::configure();
        $this->setDescription('Publishes the necessary configurations for the Matrix lib');
    }

    /**
     * Handle the execution of the command.
     */
    public function handle(): void
    {
        $this->publishMigrations();
        $this->output->writeln('<info>Publication completed!</info>');
    }

    /**
     * Publish the migration files from the Matrix library to the user's project directory.
     */
    protected function publishMigrations(): void
    {
        $projectRoot = $this->getProjectRootPath();
        $migrationsSourcePath = $projectRoot . '/vendor/ananiaslitz/matrix/database/migrations/';
        $migrationsDestinationPath = $projectRoot . '/migrations/';

        foreach (['warden', 'tenant'] as $type) {
            $sourcePath = $migrationsSourcePath . $type;
            $destinationPath = $migrationsDestinationPath . $type;

            if (!is_dir($sourcePath)) {
                $this->output->writeln("<error>Source directory $sourcePath does not exist.</error>");
                continue;
            }

            if (!is_dir($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $files = scandir($sourcePath);
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') continue; // Ignora diretórios . e ..

                $srcFile = $sourcePath . '/' . $file;
                $destFile = $destinationPath . '/' . $file;

                if (!file_exists($destFile) || $this->input->getOption('force')) {
                    copy($srcFile, $destFile);
                    $this->output->writeln("<info>Copied: $file</info>");
                } else {
                    $this->output->writeln("<comment>Already exists (not overwritten without --force): $file</comment>");
                }
            }
        }
    }

    /**
     * Get the project's root path.
     *
     * @return string The path to the project's root directory.
     */
    protected function getProjectRootPath(): string
    {
        return dirname(__DIR__, 4);
    }
}

