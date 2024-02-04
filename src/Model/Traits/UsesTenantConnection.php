<?php

namespace Ananiaslitz\Matrix\Model\Traits;

use Ananiaslitz\Matrix\Tenant\TenantManager;

trait UsesTenantConnection
{
    /**
     * Bootstrap method for the trait to configure tenant-specific database connections.
     *
     * This method is intended to be used within a trait and is typically added to models that need to set their database connection dynamically based on the current tenant. It registers the necessary callbacks to ensure that the model's connection is properly set for retrieval and creation.
     *
     * @return void
     *
     * @example
     * ```php
     * class TenantAwareModel extends Model
     * {
     *     use UsesTenantConnection;
     * }
     *
     * // In your Model, you can simply use this trait, and the tenant-specific connection will be set automatically.
     * ```
     */
    protected static function bootUsesTenantConnection(): void
    {
        static::retrieved(function ($model) {
            $model->setConnectionForTenant();
        });

        static::creating(function ($model) {
            $model->setConnectionForTenant();
        });
    }

    /**
     * Set the model's database connection to the connection of the current tenant.
     *
     * This method is responsible for dynamically configuring the database connection of the model to match the connection of the currently selected tenant in a multi-tenant application.
     *
     * @throws \RuntimeException If no tenant is set for the model.
     *
     * @example
     * ```php
     * $exampleModel = new ExampleModel();
     * $exampleModel->setConnectionForTenant(); // Set the model's connection to the current tenant's connection.
     *
     * $data = $exampleModel->all(); // Retrieve data from the current tenant's database.
     * ```
     */
    protected function setConnectionForTenant()
    {
        $tenantManager = \Hyperf\Support\make(TenantManager::class);
        if ($tenantManager->getCurrentTenant()) {
            $this->connection = $tenantManager->getCurrentTenant()->getConnectionName();
        } else {
            throw new \RuntimeException('No tenant set for model ' . static::class);
        }
    }
}
