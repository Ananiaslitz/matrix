<?php

declare(strict_types=1);

namespace Ananiaslitz\Matrix\Middleware;

use Ananiaslitz\Matrix\Tenant\TenantFinderInterface;
use Ananiaslitz\Matrix\Tenant\TenantManager;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Resolves and activates the current tenant for every incoming HTTP request.
 *
 * Pipeline:
 *  1. Uses the injected TenantFinderInterface (default: ChainedTenantFinder)
 *     to resolve a TenantData from the request.
 *  2. Passes the TenantData to TenantManager::setTenant() which configures
 *     the dynamic database connection and stores it in the coroutine context.
 *  3. Proceeds with request handling. Because TenantContext is coroutine-scoped,
 *     cleanup is automatic when the coroutine ends.
 *
 * If no tenant is found, a 404 JSON response is returned immediately.
 */
class TenantMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly TenantFinderInterface $tenantFinder,
        private readonly TenantManager $tenantManager,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $tenantData = $this->tenantFinder->findTenant($request);

        if ($tenantData === null) {
            return $this->tenantNotFoundResponse();
        }

        $this->tenantManager->setTenant($tenantData);

        return $handler->handle($request);
    }

    private function tenantNotFoundResponse(): ResponseInterface
    {
        $body = json_encode([
            'error' => 'Tenant not found.',
            'message' => 'Could not resolve a tenant from this request.',
        ]);

        return (new \Hyperf\HttpMessage\Server\Response())
            ->withStatus(404)
            ->withHeader('Content-Type', 'application/json')
            ->withBody(new SwooleStream($body));
    }
}
