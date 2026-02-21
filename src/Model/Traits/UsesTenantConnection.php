<?php

declare(strict_types=1);

namespace Ananiaslitz\Matrix\Model\Traits;

use Ananiaslitz\Matrix\Tenant\TenantContext;

/**
 * Automatically sets the Eloquent model's database connection to the
 * active tenant's connection for the current coroutine.
 *
 * Models that belong to tenant-scoped data should use this trait.
 * The connection key is resolved from TenantContext — which is stored
 * per-coroutine — so this is fully safe under Swoole concurrency.
 *
 * @example
 * class Order extends Model {
 *     use UsesTenantConnection;
 * }
 */
trait UsesTenantConnection
{
    public static function bootUsesTenantConnection(): void
    {
        $setConnection = static function ($model) {
            $tenant = TenantContext::get();

            if ($tenant === null) {
                throw new \RuntimeException(
                    sprintf(
                        'No tenant context set. Cannot resolve connection for model [%s]. '
                        . 'Ensure TenantMiddleware is registered and the tenant was resolved.',
                        static::class
                    )
                );
            }

            $model->setConnection($tenant->connectionName());
        };

        static::retrieved($setConnection);
        static::creating($setConnection);
        static::updating($setConnection);
    }

    /**
     * Override newQuery to ensure all queries from this model use the tenant connection.
     */
    public function newQuery()
    {
        $tenant = TenantContext::get();

        if ($tenant !== null) {
            $this->setConnection($tenant->connectionName());
        }

        return parent::newQuery();
    }
}
