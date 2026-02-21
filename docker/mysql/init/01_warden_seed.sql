CREATE TABLE IF NOT EXISTS `tenants` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`          VARCHAR(255) NOT NULL,
    `domain`        VARCHAR(255) DEFAULT NULL,
    `driver`        VARCHAR(50)  NOT NULL DEFAULT 'mysql',
    `host`          VARCHAR(255) NOT NULL,
    `port`          SMALLINT UNSIGNED NOT NULL DEFAULT 3306,
    `database`      VARCHAR(255) NOT NULL,
    `username`      VARCHAR(255) NOT NULL,
    `password`      VARCHAR(255) NOT NULL,
    `schema_prefix` VARCHAR(255) DEFAULT NULL,
    `is_active`     TINYINT(1)   NOT NULL DEFAULT 1,
    `config`        JSON         DEFAULT NULL,
    `created_at`    TIMESTAMP    NULL DEFAULT NULL,
    `updated_at`    TIMESTAMP    NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `tenants_name_unique` (`name`),
    UNIQUE KEY `tenants_domain_unique` (`domain`),
    KEY `tenants_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `tenants`
    (`name`, `domain`, `driver`, `host`, `port`, `database`, `username`, `password`, `is_active`)
VALUES
    ('ACME Corp', 'acme', 'mysql', 'tenant_example', 3306, 'tenant_acme', 'matrix', 'secret', 1);
