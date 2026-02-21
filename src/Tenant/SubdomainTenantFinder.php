<?php

declare(strict_types=1);

namespace Ananiaslitz\Matrix\Tenant;

use Ananiaslitz\Matrix\Model\Tenant;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Identifies the tenant by extracting the subdomain from the request Host header.
 *
 * For example, given a Host of "acme.myapp.com", the subdomain "acme" is
 * extracted and matched against the `domain` column of the tenants table.
 *
 * If the host has no subdomain (e.g. "myapp.com" or "localhost"), this finder
 * returns null and the chain falls through.
 */
class SubdomainTenantFinder implements TenantFinderInterface
{
    public function findTenant(ServerRequestInterface $request): ?TenantData
    {
        $host = $request->getUri()->getHost();

        $host = explode(':', $host)[0];

        $parts = explode('.', $host);

        if (count($parts) < 3) {
            return null;
        }

        $subdomain = $parts[0];

        if ($subdomain === 'www' || $subdomain === '') {
            return null;
        }

        /** @var Tenant|null $tenant */
        $tenant = Tenant::query()
            ->where('domain', $subdomain)
            ->where('is_active', true)
            ->first();

        if (!$tenant) {
            return null;
        }

        return TenantData::fromModel($tenant);
    }
}
