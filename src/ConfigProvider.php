<?php

declare(strict_types=1);

namespace Ananiaslitz\Matrix;

use Ananiaslitz\Matrix\Console\Commands\PublishCommand;
use Ananiaslitz\Matrix\Middleware\TenantMiddleware;
use Ananiaslitz\Matrix\Tenant\ChainedTenantFinder;
use Ananiaslitz\Matrix\Tenant\HeaderTenantFinder;
use Ananiaslitz\Matrix\Tenant\SubdomainTenantFinder;
use Ananiaslitz\Matrix\Tenant\TenantFinderInterface;

/**
 * Hyperf ConfigProvider for the Matrix library.
 *
 * Hyperf auto-discovers this class via the "extra.hyperf.config" key
 * in composer.json and calls __invoke() during application bootstrap.
 *
 * This registers:
 *  - DI bindings (TenantFinderInterface → ChainedTenantFinder)
 *  - Commands (matrix:publish)
 *  - Publish targets (config + migrations)
 */
class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                TenantFinderInterface::class => static function ($container) {
                    return new ChainedTenantFinder([
                        $container->get(HeaderTenantFinder::class),
                        $container->get(SubdomainTenantFinder::class),
                    ]);
                },
            ],

            'commands' => [
                PublishCommand::class,
            ],

            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'Matrix configuration file.',
                    'source' => __DIR__ . '/config/matrix.php',
                    'destination' => BASE_PATH . '/config/autoload/matrix.php',
                ],
                [
                    'id' => 'migrations',
                    'description' => 'Matrix tenants table migration.',
                    'source' => __DIR__ . '/database/migrations/',
                    'destination' => BASE_PATH . '/migrations/',
                ],
            ],
        ];
    }
}
