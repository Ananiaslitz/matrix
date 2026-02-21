<?php

declare(strict_types=1);

namespace Ananiaslitz\Matrix\Exceptions;

class TenantNotFoundException extends \RuntimeException implements \Throwable
{
    public function __construct(string|int|null $tenantId = null, ?\Throwable $previous = null)
    {
        $message = $tenantId !== null
            ? "Tenant '{$tenantId}' not found."
            : 'Tenant not found.';

        parent::__construct($message, 404, $previous);
    }
}