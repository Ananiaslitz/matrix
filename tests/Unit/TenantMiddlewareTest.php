<?php

declare(strict_types=1);

namespace Ananiaslitz\Matrix\Tests\Unit;

use Ananiaslitz\Matrix\Middleware\TenantMiddleware;
use Ananiaslitz\Matrix\Tenant\TenantContext;
use Ananiaslitz\Matrix\Tenant\TenantData;
use Ananiaslitz\Matrix\Tenant\TenantFinderInterface;
use Ananiaslitz\Matrix\Tenant\TenantManager;
use Hyperf\Contract\ConfigInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class TenantMiddlewareTest extends TestCase
{
    protected function tearDown(): void
    {
        TenantContext::clear();
    }

    private function makeTenant(int $id = 1): TenantData
    {
        return new TenantData(
            id: $id,
            name: 'ACME',
            domain: 'acme',
            driver: 'mysql',
            host: 'localhost',
            port: 3306,
            database: 'acme_db',
            username: 'root',
            password: 'secret',
            schemaPrefix: null,
            isActive: true,
        );
    }

    private function makeManager(): TenantManager
    {
        $config = $this->createMock(ConfigInterface::class);
        $config->method('get')->willReturn('database');
        $config->method('set');
        return new TenantManager($config);
    }

    public function testCallsHandlerWhenTenantFound(): void
    {
        $tenant = $this->makeTenant(1);
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $finder = $this->createMock(TenantFinderInterface::class);
        $finder->method('findTenant')->willReturn($tenant);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $middleware = new TenantMiddleware($finder, $this->makeManager());
        $result = $middleware->process($request, $handler);

        $this->assertSame($response, $result);
    }

    public function testSetsTenantContextWhenTenantFound(): void
    {
        $tenant = $this->makeTenant(55);
        $request = $this->createMock(ServerRequestInterface::class);

        $finder = $this->createMock(TenantFinderInterface::class);
        $finder->method('findTenant')->willReturn($tenant);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($this->createMock(ResponseInterface::class));

        $middleware = new TenantMiddleware($finder, $this->makeManager());
        $middleware->process($request, $handler);

        $this->assertSame(55, TenantContext::get()?->id);
    }

    public function testReturns404WhenTenantNotFound(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $finder = $this->createMock(TenantFinderInterface::class);
        $finder->method('findTenant')->willReturn(null);

        // Handler should NEVER be called when tenant is not found
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $middleware = new TenantMiddleware($finder, $this->makeManager());
        $result = $middleware->process($request, $handler);

        $this->assertSame(404, $result->getStatusCode());
    }

    public function testReturns404WithJsonContentType(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $finder = $this->createMock(TenantFinderInterface::class);
        $finder->method('findTenant')->willReturn(null);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($this->createMock(ResponseInterface::class));

        $middleware = new TenantMiddleware($finder, $this->makeManager());
        $result = $middleware->process($request, $handler);

        $this->assertSame('application/json', $result->getHeaderLine('Content-Type'));
    }
}
