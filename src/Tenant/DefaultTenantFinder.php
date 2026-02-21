<?php

declare(strict_types=1);

namespace Ananiaslitz\Matrix\Tenant;

/**
 * @deprecated Use SubdomainTenantFinder instead.
 *
 * This class is kept for backwards compatibility with existing configurations
 * that reference DefaultTenantFinder::class. New code should use
 * SubdomainTenantFinder directly.
 */
class DefaultTenantFinder extends SubdomainTenantFinder
{
}
