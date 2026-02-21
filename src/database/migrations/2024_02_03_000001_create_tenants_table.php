<?php

declare(strict_types=1);

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

/**
 * Creates the central tenants registry table.
 *
 * This table lives in the "warden" connection (the central database).
 * It is the single source of truth for all registered tenants.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::connection('warden')->create('tenants', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->unique()->comment('Human-readable tenant name');
            $table->string('domain')->unique()->nullable()->comment('Tenant domain or subdomain identifier');
            $table->string('driver')->default('mysql')->comment('Database driver: mysql, pgsql, sqlite');
            $table->string('host')->comment('Database host');
            $table->unsignedSmallInteger('port')->default(3306);
            $table->string('database')->comment('Database name (for database strategy)');
            $table->string('username');
            $table->string('password');
            $table->string('schema_prefix')->nullable()->comment('Schema prefix (for schema strategy)');
            $table->boolean('is_active')->default(true)->index();
            $table->json('config')->nullable()->comment('Arbitrary tenant configuration JSON');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('warden')->dropIfExists('tenants');
    }
};
