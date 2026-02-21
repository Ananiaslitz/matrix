<?php

declare(strict_types=1);

namespace Ananiaslitz\Matrix\Tenant;

use Hyperf\Contract\ConfigInterface;
use Ananiaslitz\Matrix\Exceptions\TenantNotFoundException;
use Ananiaslitz\Matrix\Model\Tenant;

/**
 * Manages tenant database connections in a coroutine-safe manner.
 *
 * Supports two isolation strategies:
 *  - "database": each tenant has its own database (host, credentials can differ).
 *  - "schema":   all tenants share the same database but use different schemas/prefixes.
 *
 * The active TenantData is stored per-coroutine via TenantContext so that
 * concurrent requests never interfere with each other.
 */
class TenantManager
{
    public function __construct(
        private readonly ConfigInterface $config,
    ) {
    }

    /**
     * Resolve a Tenant model by ID, convert to TenantData, configure the
     * dynamic connection and store it in the coroutine context.
     */
    public function setTenantById(int $tenantId): TenantData
    {
        /** @var Tenant|null $tenant */
        $tenant = Tenant::query()->find($tenantId);

        if (!$tenant) {
            throw new TenantNotFoundException($tenantId);
        }

        return $this->setTenant($this->toTenantData($tenant));
    }

    /**
     * Configure the dynamic connection from a TenantData and store it in
     * the coroutine context for the duration of the current request.
     */
    public function setTenant(TenantData $tenantData): TenantData
    {
        $strategy = $this->config->get('matrix.strategy', 'database');

        match ($strategy) {
            'schema' => $this->applySchemaStrategy($tenantData),
            default => $this->applyDatabaseStrategy($tenantData),
        };

        TenantContext::set($tenantData);

        return $tenantData;
    }

    /**
     * Return the current tenant from the coroutine context, or null if not set.
     */
    public function getCurrentTenant(): ?TenantData
    {
        return TenantContext::get();
    }


    /**
     * Strategy: each tenant has dedicated database credentials.
     * We register a named connection config for this tenant so that the
     * Hyperf connection pool can create a pool keyed to this tenant.
     */
    private function applyDatabaseStrategy(TenantData $tenantData): void
    {
        $connectionName = $tenantData->connectionName();

        $this->config->set("databases.{$connectionName}", $tenantData->toConnectionArray());
    }

    /**
     * Strategy: all tenants share the same database server but use
     * different schema prefixes (e.g. PostgreSQL search_path or a table prefix).
     * We keep the base connection but override the prefix/search_path.
     */
    private function applySchemaStrategy(TenantData $tenantData): void
    {
        $baseName = $this->config->get('matrix.tenant_connection', 'tenant');
        $baseConfig = $this->config->get("databases.{$baseName}", []);

        $connectionName = $tenantData->connectionName();

        $schemaConfig = array_merge($baseConfig, [
            'prefix' => $tenantData->schemaPrefix . '_',
            'search_path' => $tenantData->schemaPrefix,
        ]);

        $this->config->set("databases.{$connectionName}", $schemaConfig);
    }

    /**
     * Map a Tenant Eloquent model to the TenantData value object.
     */
    private function toTenantData(Tenant $tenant): TenantData
    {
        return TenantData::fromModel($tenant);
    }
}
