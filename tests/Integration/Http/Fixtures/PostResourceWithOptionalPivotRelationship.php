<?php

namespace Illuminate\Tests\Integration\Http\Fixtures;

use Illuminate\Http\Request;

class PostResourceWithOptionalPivotRelationship extends PostResource
{
    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            'subscription' => $this->whenPivotLoaded(Subscription::class, function () {
                return [
                    'foo' => 'bar',
                ];
            }),
            'custom_subscription' => $this->whenPivotLoadedAs('accessor', Subscription::class, function () {
                return [
                    'foo' => 'bar',
                ];
            }),
        ];
    }
}
