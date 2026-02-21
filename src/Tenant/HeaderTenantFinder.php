<?php

declare(strict_types=1);

namespace Ananiaslitz\Matrix\Tenant;

use Ananiaslitz\Matrix\Model\Tenant;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Identifies the tenant by the X-Tenant-ID HTTP header.
 *
 * The header value is matched against the tenant's id or domain column.
 * This is useful for API-style multi-tenancy where the client explicitly
 * declares its tenant context per request.
 */
class HeaderTenantFinder implements TenantFinderInterface
{
    public const HEADER_NAME = 'X-Tenant-ID';

    public function findTenant(ServerRequestInterface $request): ?TenantData
    {
        $headerValues = $request->getHeader(self::HEADER_NAME);

        if (empty($headerValues)) {
            return null;
        }

        $value = trim($headerValues[0]);

        if ($value === '') {
            return null;
        }

        /** @var Tenant|null $tenant */
        $tenant = Tenant::query()
            ->where(function ($q) use ($value) {
                $q->where('domain', $value);
                if (is_numeric($value)) {
                    $q->orWhere('id', (int) $value);
                }
            })
            ->where('is_active', true)
            ->first();

        if (!$tenant) {
            return null;
        }

        return TenantData::fromModel($tenant);
    }
}
