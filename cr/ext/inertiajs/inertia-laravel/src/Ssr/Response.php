<?php

namespace Inertia\Ssr;

class Response
{
    /**
     * @var string
     */
    public $head;

    /**
     * @var string
     */
    public $body;

    /**
     * Prepare the Inertia Server Side Rendering (SSR) response.
     *
     * @param  string  $head
     * @param  string  $body
     */
    public function __construct(/*string */$head, /*string */$body)
    {
        $body = cast_to_string($body);

        $head = cast_to_string($head);

        $this->head = $head;
        $this->body = $body;
    }
}
