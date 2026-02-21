<?php

declare(strict_types=1);

namespace Ananiaslitz\Matrix\Model;

use Ananiaslitz\Matrix\Model\Traits\UsesWardenConnection;
use Ananiaslitz\Matrix\Tenant\TenantData;
use Hyperf\Database\Model\Model;

/**
 * Central (warden) Tenant model.
 *
 * This model always uses the "warden" connection — the central database that
 * stores all tenant registry data. It must NEVER be used with a tenant
 * connection to avoid circular dependency.
 *
 * @property int         $id
 * @property string      $name
 * @property string|null $domain
 * @property string      $driver
 * @property string      $host
 * @property int         $port
 * @property string      $database
 * @property string      $username
 * @property string      $password
 * @property string|null $schema_prefix
 * @property bool        $is_active
 * @property array|null  $config
 */
class Tenant extends Model
{
    use UsesWardenConnection;

    protected array $fillable = [
        'name',
        'domain',
        'driver',
        'database',
        'host',
        'port',
        'username',
        'password',
        'schema_prefix',
        'is_active',
        'config',
    ];

    protected array $casts = [
        'port' => 'integer',
        'is_active' => 'boolean',
        'config' => 'array',
    ];

    /**
     * Convert this model to the TenantData value object.
     * Prefer using TenantData::fromModel() to keep mapping logic in one place.
     */
    public function toTenantData(): TenantData
    {
        return TenantData::fromModel($this);
    }
}