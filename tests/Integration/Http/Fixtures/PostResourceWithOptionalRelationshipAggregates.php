<?php

namespace Illuminate\Tests\Integration\Http\Fixtures;

use Illuminate\Http\Request;

class PostResourceWithOptionalRelationshipAggregates extends PostResource
{
    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'average_rating' => $this->whenAggregated('comments', 'rating', 'avg'),
            'minimum_rating' => $this->whenAggregated('comments', 'rating', 'min'),
            'maximum_rating' => $this->whenAggregated('comments', 'rating', 'max', function ($avg) { return "$avg ratings"; }, 'Default Value'),
        ];
    }
}
