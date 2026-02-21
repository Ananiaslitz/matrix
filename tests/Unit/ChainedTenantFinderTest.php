<?php

declare(strict_types=1);

namespace Ananiaslitz\Matrix\Tests\Unit;

use Ananiaslitz\Matrix\Tenant\ChainedTenantFinder;
use Ananiaslitz\Matrix\Tenant\TenantData;
use Ananiaslitz\Matrix\Tenant\TenantFinderInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class ChainedTenantFinderTest extends TestCase
{
    private function makeRequest(): ServerRequestInterface
    {
        return $this->createMock(ServerRequestInterface::class);
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

    private function finderReturning(?TenantData $tenant): TenantFinderInterface
    {
        $finder = $this->createMock(TenantFinderInterface::class);
        $finder->method('findTenant')->willReturn($tenant);
        return $finder;
    }

    public function testReturnsNullWhenNoFindersRegistered(): void
    {
        $chain = new ChainedTenantFinder([]);
        $result = $chain->findTenant($this->makeRequest());
        $this->assertNull($result);
    }

    public function testReturnsNullWhenAllFindersReturnNull(): void
    {
        $chain = new ChainedTenantFinder([
            $this->finderReturning(null),
            $this->finderReturning(null),
        ]);

        $this->assertNull($chain->findTenant($this->makeRequest()));
    }

    public function testReturnsFirstNonNullResult(): void
    {
        $tenant = $this->makeTenant(5);

        $chain = new ChainedTenantFinder([
            $this->finderReturning(null),
            $this->finderReturning($tenant),
            $this->finderReturning($this->makeTenant(99)), // should never be reached
        ]);

        $result = $chain->findTenant($this->makeRequest());
        $this->assertNotNull($result);
        $this->assertSame(5, $result->id);
    }

    public function testStopsAfterFirstMatch(): void
    {
        $tenant = $this->makeTenant(1);
        $shouldNotRun = $this->createMock(TenantFinderInterface::class);
        $shouldNotRun->expects($this->never())->method('findTenant');

        $chain = new ChainedTenantFinder([
            $this->finderReturning($tenant),
            $shouldNotRun,
        ]);

        $chain->findTenant($this->makeRequest());
    }

    public function testFirstFinderReturnsResult(): void
    {
        $tenant = $this->makeTenant(3);

        $chain = new ChainedTenantFinder([
            $this->finderReturning($tenant),
        ]);

        $result = $chain->findTenant($this->makeRequest());
        $this->assertSame(3, $result->id);
    }
}
