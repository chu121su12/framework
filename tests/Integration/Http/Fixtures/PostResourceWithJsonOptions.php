<?php

namespace Illuminate\Tests\Integration\Http\Fixtures;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResourceWithJsonOptions extends JsonResource
{
    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'reading_time' => $this->reading_time,
        ];
    }

    public function jsonOptions()
    {
        return JSON_PRESERVE_ZERO_FRACTION;
    }
}
