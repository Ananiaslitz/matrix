<?php

declare(strict_types=1);

namespace Ananiaslitz\Matrix\Tests\Unit;

use Ananiaslitz\Matrix\Tenant\TenantData;
use PHPUnit\Framework\TestCase;

class TenantDataTest extends TestCase
{
    private function makeTenantData(array $overrides = []): TenantData
    {
        return new TenantData(
            id: $overrides['id'] ?? 1,
            name: $overrides['name'] ?? 'ACME',
            domain: $overrides['domain'] ?? 'acme',
            driver: $overrides['driver'] ?? 'mysql',
            host: $overrides['host'] ?? 'localhost',
            port: $overrides['port'] ?? 3306,
            database: $overrides['database'] ?? 'acme_db',
            username: $overrides['username'] ?? 'root',
            password: $overrides['password'] ?? 'secret',
            schemaPrefix: $overrides['schemaPrefix'] ?? null,
            isActive: $overrides['isActive'] ?? true,
            config: $overrides['config'] ?? [],
        );
    }

    public function testConnectionNameIncludesTenantId(): void
    {
        $tenant = $this->makeTenantData(['id' => 42]);
        $this->assertSame('tenant_42', $tenant->connectionName());
    }

    public function testToConnectionArrayHasRequiredKeys(): void
    {
        $tenant = $this->makeTenantData();
        $conn = $tenant->toConnectionArray();

        $this->assertArrayHasKey('driver', $conn);
        $this->assertArrayHasKey('host', $conn);
        $this->assertArrayHasKey('port', $conn);
        $this->assertArrayHasKey('database', $conn);
        $this->assertArrayHasKey('username', $conn);
        $this->assertArrayHasKey('password', $conn);
    }

    public function testPropertiesAreImmutable(): void
    {
        $tenant = $this->makeTenantData(['name' => 'Original']);

        // readonly properties cannot be reassigned — verify via reflection
        $ref = new \ReflectionProperty($tenant, 'name');
        $this->assertTrue($ref->isReadOnly());
        $this->assertSame('Original', $tenant->name);
    }

    public function testIsActiveDefaultsToTrue(): void
    {
        $tenant = $this->makeTenantData();
        $this->assertTrue($tenant->isActive);
    }
}
