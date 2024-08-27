<?php

namespace Illuminate\Tests\Routing;

use Attribute;

enum CategoryEnum
{
    case People;
    case Fruits;
}

enum CategoryBackedEnum: string
{
    case People = 'people';
    case Fruits = 'fruits';
}

enum RouteNameEnum: string
{
    case UserIndex = 'users.index';
}

enum RouteDomainEnum: string
{
    case DashboardDomain = 'dashboard.myapp.com';
}

enum IntegerEnum: int
{
    case One = 1;
    case Two = 2;
}

#[Attribute(Attribute::TARGET_PARAMETER)]
final class RoutingTestOnTenant
{
    public function __construct(
        public readonly RoutingTestTenant $tenant
    ) {
    }
}

enum RoutingTestTenant
{
    case TenantA;
    case TenantB;
}

final class RoutingTestHasTenantImpl
{
    public ?RoutingTestTenant $tenant = null;

    public function onTenant(RoutingTestTenant $tenant): void
    {
        $this->tenant = $tenant;
    }
}
