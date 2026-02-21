<?php

declare(strict_types=1);

namespace Ananiaslitz\Matrix\Console\Commands;

use Hyperf\Command\Command as HyperfCommand;
use Symfony\Component\Console\Input\InputOption;

/**
 * Publishes Matrix configuration and migration files to the host project.
 */
class PublishCommand extends HyperfCommand
{
    protected ?string $name = 'matrix:publish';

    public function configure(): void
    {
        parent::configure();
        $this->setDescription('Publishes Matrix config and migration files to your project.');
        $this->addOption('force', 'f', InputOption::VALUE_NONE, 'Overwrite existing files.');
    }

    public function handle(): void
    {
        $this->publishConfig();
        $this->publishMigrations();
        $this->output->writeln('<info>Matrix: publication completed!</info>');
    }

    protected function publishConfig(): void
    {
        $source = __DIR__ . '/../../../config/matrix.php';
        $destination = $this->getProjectRootPath() . '/config/autoload/matrix.php';

        $this->publishFile($source, $destination, 'matrix.php (config)');
    }

    protected function publishMigrations(): void
    {
        $source = __DIR__ . '/../../../database/migrations/';
        $destination = $this->getProjectRootPath() . '/migrations/';

        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        if (!is_dir($source)) {
            $this->output->writeln("<error>Matrix: migration source directory not found: {$source}</error>");
            return;
        }

        foreach (new \DirectoryIterator($source) as $file) {
            if ($file->isDot() || !$file->isFile()) {
                continue;
            }

            $this->publishFile(
                $file->getPathname(),
                $destination . $file->getFilename(),
                $file->getFilename()
            );
        }
    }

    protected function publishFile(string $source, string $destination, string $label): void
    {
        $force = $this->input->getOption('force');

        if (!file_exists($source)) {
            $this->output->writeln("<error>Matrix: source not found: {$source}</error>");
            return;
        }

        if (file_exists($destination) && !$force) {
            $this->output->writeln("<comment>Matrix: already exists (use --force to overwrite): {$label}</comment>");
            return;
        }

        $dir = dirname($destination);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        copy($source, $destination);
        $this->output->writeln("<info>Matrix: published {$label}</info>");
    }

    protected function getProjectRootPath(): string
    {
        return defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 5);
    }
}
