<?php

declare(strict_types=1);

/**
 * Default Matrix configuration file.
 *
 * Publish this file to your project with:
 *   php bin/hyperf.php matrix:publish
 *
 * Then customize as needed at config/autoload/matrix.php
 */

use Ananiaslitz\Matrix\Tenant\ChainedTenantFinder;
use function Hyperf\Support\env;

return [
    /*
    |--------------------------------------------------------------------------
    | Tenant Finder
    |--------------------------------------------------------------------------
    | The class responsible for resolving the current tenant from the request.
    | The default ChainedTenantFinder tries X-Tenant-ID header first,
    | then falls back to subdomain identification.
    |
    | You can swap this for any class implementing TenantFinderInterface,
    | or configure the chain via the DI container in config/dependencies.php.
    */
    'tenant_finder' => ChainedTenantFinder::class,

    /*
    |--------------------------------------------------------------------------
    | Isolation Strategy
    |--------------------------------------------------------------------------
    | "database": each tenant uses its own database (host/credentials from
    |             the tenants table). Best for strong isolation.
    |
    | "schema":   all tenants share the same database server. The tenant's
    |             schema_prefix column is used as a table prefix or
    |             PostgreSQL search_path. Best for operational simplicity.
    */
    'strategy' => env('MATRIX_STRATEGY', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Warden Connection
    |--------------------------------------------------------------------------
    | The name of the database connection that holds the tenants table.
    | This is your central/management database. Configure it as a normal
    | Hyperf database connection in config/autoload/databases.php.
    */
    'warden_connection' => env('MATRIX_WARDEN_CONNECTION', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Tenant Connection Prefix
    |--------------------------------------------------------------------------
    | When using the "schema" strategy, this is the base connection from which
    | the per-tenant connection config is cloned (with a different prefix).
    */
    'tenant_connection' => env('MATRIX_TENANT_CONNECTION', 'tenant'),
];
