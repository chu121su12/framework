<?php

namespace Illuminate\Tests\Integration\Http\Fixtures;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SerializablePostResource extends JsonResource
{
    public function toArray(Request $request)
    {
        return new JsonSerializableResource($this);
    }
}
