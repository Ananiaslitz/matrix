<?php

declare(strict_types=1);

namespace Ananiaslitz\Matrix\Tests\Unit;

use Ananiaslitz\Matrix\Tenant\TenantContext;
use Ananiaslitz\Matrix\Tenant\TenantData;
use PHPUnit\Framework\TestCase;

class TenantContextTest extends TestCase
{
    protected function tearDown(): void
    {
        // Always clean up to avoid test pollution
        TenantContext::clear();
    }

    private function makeTenant(int $id = 1): TenantData
    {
        return new TenantData(
            id: $id,
            name: 'Test',
            domain: 'test',
            driver: 'mysql',
            host: 'localhost',
            port: 3306,
            database: 'test_db',
            username: 'root',
            password: 'secret',
            schemaPrefix: null,
            isActive: true,
        );
    }

    public function testHasReturnsFalseWhenNoTenantSet(): void
    {
        $this->assertFalse(TenantContext::has());
    }

    public function testGetReturnsNullWhenNoTenantSet(): void
    {
        $this->assertNull(TenantContext::get());
    }

    public function testSetAndGetRoundTrip(): void
    {
        $tenant = $this->makeTenant(7);
        TenantContext::set($tenant);

        $retrieved = TenantContext::get();
        $this->assertNotNull($retrieved);
        $this->assertSame(7, $retrieved->id);
        $this->assertSame('Test', $retrieved->name);
    }

    public function testHasReturnsTrueAfterSet(): void
    {
        TenantContext::set($this->makeTenant());
        $this->assertTrue(TenantContext::has());
    }

    public function testClearRemovesTenant(): void
    {
        TenantContext::set($this->makeTenant());
        TenantContext::clear();

        $this->assertFalse(TenantContext::has());
        $this->assertNull(TenantContext::get());
    }

    public function testSecondSetOverwritesFirst(): void
    {
        TenantContext::set($this->makeTenant(1));
        TenantContext::set($this->makeTenant(2));

        $this->assertSame(2, TenantContext::get()->id);
    }
}
