<?php

namespace Illuminate\Tests\Integration\Http\Fixtures;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResourceWithOptionalMerging extends JsonResource
{
    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            $this->mergeWhen(false, ['first' => 'value']),
            $this->mergeWhen(true, ['second' => 'value']),
        ];
    }
}
