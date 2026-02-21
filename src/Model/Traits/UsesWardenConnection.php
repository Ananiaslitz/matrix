<?php

declare(strict_types=1);

namespace Ananiaslitz\Matrix\Model\Traits;

/**
 * Forces the Eloquent model to always use the "warden" database connection.
 *
 * Apply this trait to models that live in the central database (e.g. the
 * Tenant model itself), ensuring they never accidentally resolve to a
 * tenant-scoped connection.
 */
trait UsesWardenConnection
{
    public function initializeUsesWardenConnection(): void
    {
        $this->connection = 'warden';
    }
}
