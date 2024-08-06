<?php

namespace Illuminate\Tests\Integration\Http\Fixtures;

class PostResourceWithOptionalRelationshipUsingNamedParameters extends PostResource
{
    public function toArray(\Illuminate\Http\Request $request)
    {
        return [
            'id' => $this->id,
            'author' => new AuthorResource($this->whenLoaded('author')),
            'author_defaulting_to_null' => new AuthorResource($this->whenLoaded('author', /*$value = */null, /*default: */null)),
            'author_name' => $this->whenLoaded('author', function ($author) { return $author->name; }, 'Anonymous'),
        ];
    }
}
