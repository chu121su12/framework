<?php

/*declare(strict_types=1);*/

namespace Illuminate\Tests\Integration\Http\Fixtures;

class PostResourceWithOptionalRelationshipExists extends PostResource
{
    public function toArray(\Illuminate\Http\Request $request)
    {
        return [
            'id' => $this->id,
            'has_authors' => $this->whenExistsLoaded('authors'),
            'has_favourited_posts' => $this->whenExistsLoaded('favouritedPosts', function ($exists) { return $exists ? 'Yes' : 'No'; }, 'No'),
            'comment_exists' => $this->whenExistsLoaded('comments'),
        ];
    }
}
