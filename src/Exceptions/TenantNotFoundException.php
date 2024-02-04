<?php

/**
 * Exception class to represent a Tenant not found error.
 *
 * This exception is thrown when a requested tenant cannot be found, typically used within a multi-tenant application to handle cases where a specific tenant is not available or does not exist.
 *
 * @package Ananiaslitz\Matrix\Exceptions
 */
namespace Ananiaslitz\Matrix\Exceptions;

class TenantNotFoundException extends \Exception implements \Throwable
{
    /**
     * TenantNotFoundException constructor.
     *
     * @param \Throwable|null $previous The previous throwable used for chaining exceptions.
     */
    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct("Tenant not found", 404, $previous);
    }
}