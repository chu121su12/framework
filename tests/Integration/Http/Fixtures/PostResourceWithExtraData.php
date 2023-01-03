<?php

namespace Illuminate\Tests\Integration\Http\Fixtures;

use Illuminate\Http\Request;

class PostResourceWithExtraData extends PostResource
{
    public function with(Request $request)
    {
        return ['foo' => 'bar'];
    }
}
