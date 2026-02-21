<?php

declare(strict_types=1);

namespace Ananiaslitz\Matrix\Tenant;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Contract for tenant resolution strategies.
 *
 * Returns a fully-hydrated TenantData value object (not a raw ID or Model)
 * so callers never need to do a second database query.
 */
interface TenantFinderInterface
{
    /**
     * Attempt to find the tenant from the incoming request.
     *
     * @return TenantData|null Null means this finder cannot identify the tenant.
     */
    public function findTenant(ServerRequestInterface $request): ?TenantData;
}
