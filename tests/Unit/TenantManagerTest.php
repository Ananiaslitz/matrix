<?php

declare(strict_types=1);

namespace Ananiaslitz\Matrix\Tests\Unit;

use Ananiaslitz\Matrix\Tenant\TenantContext;
use Ananiaslitz\Matrix\Tenant\TenantData;
use Ananiaslitz\Matrix\Tenant\TenantManager;
use Hyperf\Contract\ConfigInterface;
use PHPUnit\Framework\TestCase;

class TenantManagerTest extends TestCase
{
    protected function tearDown(): void
    {
        TenantContext::clear();
    }

    private function makeTenant(int $id = 1, string $strategy = 'database'): TenantData
    {
        return new TenantData(
            id: $id,
            name: 'ACME',
            domain: 'acme',
            driver: 'mysql',
            host: 'db.acme.com',
            port: 3306,
            database: 'acme_db',
            username: 'acme_user',
            password: 'acme_pass',
            schemaPrefix: $strategy === 'schema' ? 'acme' : null,
            isActive: true,
        );
    }

    private function makeConfig(string $strategy = 'database'): ConfigInterface
    {
        $config = $this->createMock(ConfigInterface::class);
        $config->method('get')
            ->willReturnCallback(function (string $key, $default = null) use ($strategy) {
                return match (true) {
                    str_starts_with($key, 'matrix.strategy') => $strategy,
                    str_starts_with($key, 'matrix.tenant_connection') => 'tenant',
                    default => $default,
                };
            });

        return $config;
    }

    public function testSetTenantStoresInContext(): void
    {
        $config = $this->createMock(ConfigInterface::class);
        $config->method('get')->willReturn('database');
        $config->expects($this->atLeastOnce())->method('set');

        $manager = new TenantManager($config);
        $tenant = $this->makeTenant(42);

        $manager->setTenant($tenant);

        $this->assertSame(42, TenantContext::get()?->id);
    }

    public function testGetCurrentTenantReturnsNullByDefault(): void
    {
        $config = $this->createMock(ConfigInterface::class);
        $manager = new TenantManager($config);

        $this->assertNull($manager->getCurrentTenant());
    }

    public function testGetCurrentTenantAfterSet(): void
    {
        $config = $this->createMock(ConfigInterface::class);
        $config->method('get')->willReturn('database');
        $config->method('set');

        $manager = new TenantManager($config);
        $tenant = $this->makeTenant(7);
        $manager->setTenant($tenant);

        $current = $manager->getCurrentTenant();
        $this->assertNotNull($current);
        $this->assertSame(7, $current->id);
    }

    public function testDatabaseStrategySetsDynamicConnectionConfig(): void
    {
        $tenant = $this->makeTenant(1, 'database');

        $config = $this->createMock(ConfigInterface::class);
        $config->method('get')->willReturn('database');

        // Assert that config->set is called with the tenant's connection key
        $config->expects($this->once())
            ->method('set')
            ->with(
                'databases.' . $tenant->connectionName(),
                $this->arrayHasKey('host')
            );

        $manager = new TenantManager($config);
        $manager->setTenant($tenant);
    }

    public function testSchemaStrategyMergesWithBaseConfig(): void
    {
        $tenant = $this->makeTenant(2, 'schema');

        $baseConfig = [
            'driver' => 'mysql',
            'host' => 'shared-db',
            'database' => 'shared',
            'username' => 'shared_user',
            'password' => 'shared_pass',
        ];

        $config = $this->createMock(ConfigInterface::class);
        $config->method('get')
            ->willReturnCallback(function (string $key, $default = null) use ($baseConfig) {
                if ($key === 'matrix.strategy')
                    return 'schema';
                if ($key === 'matrix.tenant_connection')
                    return 'tenant';
                if (str_starts_with($key, 'databases.tenant'))
                    return $baseConfig;
                return $default;
            });

        $config->expects($this->once())
            ->method('set')
            ->with(
                'databases.' . $tenant->connectionName(),
                $this->callback(function ($value) {
                    // schema strategy must merge prefix and search_path
                    return isset($value['prefix']) && isset($value['search_path']);
                })
            );

        $manager = new TenantManager($config);
        $manager->setTenant($tenant);
    }

    public function testSetTenantReturnsTenantData(): void
    {
        $config = $this->createMock(ConfigInterface::class);
        $config->method('get')->willReturn('database');
        $config->method('set');

        $manager = new TenantManager($config);
        $tenant = $this->makeTenant(99);

        $returned = $manager->setTenant($tenant);
        $this->assertSame($tenant, $returned);
    }
}
