<?php

declare(strict_types=1);

namespace Ananiaslitz\Matrix\Tenant;

use Ananiaslitz\Matrix\Model\Tenant;

/**
 * Immutable Value Object representing a resolved tenant.
 *
 * This DTO is the boundary between the infrastructure layer (Eloquent Model)
 * and the rest of the application, preventing model instances from leaking
 * into domain or application code.
 */
final class TenantData
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $domain,
        public readonly string $driver,
        public readonly string $host,
        public readonly int $port,
        public readonly string $database,
        public readonly string $username,
        public readonly string $password,
        public readonly ?string $schemaPrefix,
        public readonly bool $isActive,
        public readonly array $config = [],
    ) {
    }

    /**
     * Named constructor: build from a Tenant Eloquent model.
     * Centralizes the mapping so it's not duplicated across finders/managers.
     */
    public static function fromModel(Tenant $tenant): self
    {
        return new self(
            id: $tenant->id,
            name: $tenant->name,
            domain: $tenant->domain ?? '',
            driver: $tenant->driver ?? 'mysql',
            host: $tenant->host,
            port: (int) ($tenant->port ?? 3306),
            database: $tenant->database,
            username: $tenant->username,
            password: $tenant->password,
            schemaPrefix: $tenant->schema_prefix ?? null,
            isActive: (bool) ($tenant->is_active ?? true),
            config: is_array($tenant->config) ? $tenant->config : [],
        );
    }

    /**
     * Returns the unique connection name for this tenant.
     * Used to key the dynamic database connection in Hyperf's pool.
     */
    public function connectionName(): string
    {
        return 'tenant_' . $this->id;
    }

    /**
     * Returns the configuration array expected by Hyperf's database config.
     */
    public function toConnectionArray(): array
    {
        return [
            'driver' => $this->driver,
            'host' => $this->host,
            'port' => $this->port,
            'database' => $this->database,
            'username' => $this->username,
            'password' => $this->password,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ];
    }
}
