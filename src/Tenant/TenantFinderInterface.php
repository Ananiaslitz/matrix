<?php

/**
 * Interface for defining tenant finder implementations.
 *
 * This interface outlines the contract that tenant finder implementations must adhere to. Tenant finder classes are responsible for determining the tenant ID based on an incoming HTTP request in a multi-tenant application.
 *
 * @package Ananiaslitz\Matrix\Tenant
 */
namespace Ananiaslitz\Matrix\Tenant;

use Psr\Http\Message\ServerRequestInterface;

interface TenantFinderInterface
{
    /**
     * Find and return the ID of the tenant based on the incoming HTTP request.
     *
     * @param ServerRequestInterface $request The incoming HTTP request.
     * @return int|null The ID of the tenant if found; null if not found.
     */
    public function findTenantId(ServerRequestInterface $request): ?int;
}
