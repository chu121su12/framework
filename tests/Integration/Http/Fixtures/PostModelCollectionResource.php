<?php

namespace Illuminate\Tests\Integration\Http\Fixtures;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PostModelCollectionResource extends ResourceCollection
{
    public $collects = Post::class;

    public function toArray(Request $request)
    {
        return ['data' => $this->collection];
    }
}
