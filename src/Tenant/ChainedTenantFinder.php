<?php

declare(strict_types=1);

namespace Ananiaslitz\Matrix\Tenant;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Composite finder that tries each registered finder in order.
 *
 * The first finder that returns a non-null TenantData wins. If all finders
 * return null, this finder also returns null and the middleware will respond
 * with a 404.
 *
 * Default chain: HeaderTenantFinder → SubdomainTenantFinder
 *
 * @example
 * // Customizing the chain via DI in config/dependencies.php:
 * ChainedTenantFinder::class => fn($c) => new ChainedTenantFinder([
 *     $c->get(HeaderTenantFinder::class),
 *     $c->get(SubdomainTenantFinder::class),
 *     $c->get(MyCustomFinder::class),
 * ]),
 */
class ChainedTenantFinder implements TenantFinderInterface
{
    /** @param TenantFinderInterface[] $finders */
    public function __construct(
        private readonly array $finders,
    ) {
    }

    public function findTenant(ServerRequestInterface $request): ?TenantData
    {
        foreach ($this->finders as $finder) {
            $tenant = $finder->findTenant($request);
            if ($tenant !== null) {
                return $tenant;
            }
        }

        return null;
    }
}
