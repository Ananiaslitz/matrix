<?php

/**
 * TenantManager class for managing tenant-specific database connections.
 *
 * The TenantManager class is responsible for handling tenant-specific database connections in a multi-tenant application. It provides methods for setting the database connection dynamically based on the tenant's details, reconnecting to the tenant's database, and retrieving tenant connection details.
 *
 * @package Ananiaslitz\Matrix\Tenant
 */
namespace Ananiaslitz\Matrix\Tenant;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Database\ConnectionResolverInterface;
use Ananiaslitz\Matrix\Exceptions\TenantNotFoundException;
use Ananiaslitz\Matrix\Model\Tenant;

class TenantManager
{
    /**
     * The connection resolver instance used for managing database connections.
     *
     * @var ConnectionResolverInterface
     */
    private ConnectionResolverInterface $resolver;

    /**
     * The configuration interface for retrieving tenant-related settings.
     *
     * @var ConfigInterface
     */
    protected ConfigInterface $config;

    /**
     * TenantManager constructor.
     *
     * @param ConnectionResolverInterface $resolver The connection resolver instance.
     * @param ConfigInterface $config The configuration interface.
     */
    public function __construct(
        ConnectionResolverInterface $resolver,
        ConfigInterface $config
    ) {
        $this->resolver = $resolver;
        $this->config = $config;
    }

    /**
     * Set the database connection to match the specified tenant's connection.
     *
     * @param mixed $tenantId The identifier of the tenant whose connection should be set.
     *
     * @throws TenantNotFoundException If the specified tenant is not found.
     */
    public function setTenantConnection(int $tenantId): void
    {
        $details = $this->getTenantConnectionDetails($tenantId);

        $this->config->set('databases.tenant', [
            'driver' => $details['driver'],
            'host' => $details['host'],
            'database' => $details['database'],
            'username' => $details['username'],
            'password' => $details['password'],
            'port' => $details['port'],
        ]);

        $this->resolver->connection('tenant')->reconnect();
    }

    /**
     * Retrieve and return the connection details for the specified tenant.
     *
     * @param mixed $tenantId The identifier of the tenant.
     *
     * @return array The connection details for the specified tenant.
     *
     * @throws TenantNotFoundException If the specified tenant is not found.
     */
    protected function getTenantConnectionDetails($tenantId): array
    {
        $tenant = Tenant::query()->find($tenantId);

        if (!$tenant) {
            throw new TenantNotFoundException($tenantId);
        }

        return [
            'host' => $tenant->host,
            'database' => $tenant->database,
            'username' => $tenant->username,
            'password' => $tenant->password,
            'port' => $tenant->port,
        ];
    }
}
