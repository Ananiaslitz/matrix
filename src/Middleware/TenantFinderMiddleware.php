<?php

/**
 * Middleware for finding and setting the tenant connection in a multi-tenant application.
 *
 * This middleware is responsible for locating the appropriate tenant based on the incoming HTTP request and setting the database connection for the current request to the corresponding tenant's connection.
 *
 * @package Ananiaslitz\Matrix\Middleware
 */
namespace Ananiaslitz\Matrix\Middleware;

use Hyperf\Contract\ConfigInterface;
use Ananiaslitz\Matrix\Tenant\DefaultTenantFinder;
use Ananiaslitz\Matrix\Tenant\TenantFinderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ananiaslitz\Matrix\Tenant\TenantManager;

class TenantFinderMiddleware implements MiddlewareInterface
{
    /**
     * TenantFinderMiddleware constructor.
     *
     * @param TenantManager $tenantManager The tenant manager responsible for managing tenant connections.
     * @param ConfigInterface $config The configuration interface for retrieving tenant-related settings.
     */
    public function __construct(
        private TenantManager $tenantManager,
        private ConfigInterface $config
    ) {
    }

    /**
     * Process the incoming HTTP request, set the tenant connection, and continue with the request handling.
     *
     * @param ServerRequestInterface $request The incoming HTTP request.
     * @param RequestHandlerInterface $handler The request handler.
     * @return ResponseInterface The response to the HTTP request.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $finderClass = $this->config->get('matrix.tenant_finder', DefaultTenantFinder::class);
        /** @var TenantFinderInterface $finder */
        $finder = \Hyperf\Support\make($finderClass);

        $tenantId = $finder->findTenantId($request);
        $this->tenantManager->setTenantConnection($tenantId);
        return $handler->handle($request);
    }
}
