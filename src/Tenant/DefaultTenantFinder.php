<?php

/**
 * Default tenant finder implementation for locating the tenant based on the HTTP request.
 *
 * This class is responsible for identifying the tenant based on the incoming HTTP request, particularly by analyzing the subdomain from the host. If a matching tenant is found, its ID is returned to be used for setting the tenant's database connection.
 *
 * @package Ananiaslitz\Matrix\Tenant
 */
namespace Ananiaslitz\Matrix\Tenant;

use Hyperf\HttpServer\Contract\RequestInterface;
use Ananiaslitz\Matrix\Exceptions\TenantNotFoundException;
use Ananiaslitz\Matrix\Model\Tenant;
use Psr\Http\Message\ServerRequestInterface;

class DefaultTenantFinder implements TenantFinderInterface
{
    /**
     * The HTTP request instance used for tenant identification.
     *
     * @var RequestInterface
     */
    protected $request;

    /**
     * DefaultTenantFinder constructor.
     *
     * @param RequestInterface $request The HTTP request instance used for tenant identification.
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * Find and return the ID of the tenant based on the incoming HTTP request.
     *
     * @param ServerRequestInterface $request The incoming HTTP request.
     * @return int|null The ID of the tenant if found; null if not found.
     *
     * @throws TenantNotFoundException If the tenant is not found.
     */
    public function findTenantId(ServerRequestInterface $request): ?int
    {
        $host = $this->request->getUri()->getHost();
        $subdomain = explode('.', $host)[0];

        $tenant = Tenant::query()->where('subdomain', $subdomain)->first();

        if (!$tenant) {
            throw new TenantNotFoundException();
        }

        return $tenant->id;
    }
}
