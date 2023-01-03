<?php

namespace Illuminate\Tests\Integration\Http\Fixtures;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray(Request $request)
    {
        return ['id' => $this->id, 'title' => $this->title, 'custom' => true];
    }

    public function withResponse(Request $request, JsonResponse $response)
    {
        $response->header('X-Resource', 'True');
    }
}
