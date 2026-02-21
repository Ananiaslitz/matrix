<?php

declare(strict_types=1);

namespace Ananiaslitz\Matrix\Tenant;

use Hyperf\Context\Context;

/**
 * Stores and retrieves the current TenantData per coroutine.
 *
 * Hyperf runs on Swoole coroutines — each request executes in its own
 * coroutine. Using Hyperf's Context ensures the tenant is completely
 * isolated between concurrent requests, avoiding race conditions that
 * would occur with a global singleton or static property.
 */
final class TenantContext
{
    private const KEY = 'matrix.current_tenant';

    public static function set(TenantData $tenant): void
    {
        Context::set(self::KEY, $tenant);
    }

    public static function get(): ?TenantData
    {
        return Context::get(self::KEY);
    }

    public static function has(): bool
    {
        return Context::has(self::KEY);
    }

    public static function clear(): void
    {
        Context::set(self::KEY, null);
    }
}
