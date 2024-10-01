<?php

namespace Illuminate\Tests\Integration\Queue;

enum BackedEnumNamedRateLimited: string
{
    case FOO = 'bar';
}

enum UnitEnumNamedRateLimited
{
    case LARAVEL;
}

