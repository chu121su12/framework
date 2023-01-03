<?php

namespace Illuminate\Tests\Integration\Http\Fixtures;

use Illuminate\Http\Request;

class ResourceWithPreservedKeys extends PostResource
{
    protected $preserveKeys = true;

    public function toArray(Request $request)
    {
        return $this->resource;
    }
}
