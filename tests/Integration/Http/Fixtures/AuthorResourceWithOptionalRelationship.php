<?php

namespace Illuminate\Tests\Integration\Http\Fixtures;

use Illuminate\Http\Request;

class AuthorResourceWithOptionalRelationship extends PostResource
{
    public function toArray(Request $request)
    {
        return [
            'name' => $this->name,
            'posts_count' => $this->whenLoaded('posts', function () {
                return $this->posts->count().' posts';
            }, function () {
                return 'not loaded';
            }),
            'latest_post_title' => $this->whenLoaded('posts', function () {
                return optional($this->posts->first())->title ?: 'no posts yet';
            }, 'not loaded'),
        ];
    }
}
