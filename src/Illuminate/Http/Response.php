<?php

namespace Illuminate\Http;

use ArrayObject;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use InvalidArgumentException;
use JsonSerializable;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag5 as ResponseHeaderBag;

class Response extends SymfonyResponse
{
    use ResponseTrait, Macroable {
        Macroable::__call as macroCall;
    }

    /**
     * Create a new HTTP response.
     *
     * @param  mixed  $content
     * @param  int  $status
     * @param  array  $headers
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($content = '', $status = 200, array $headers = [])
    {
        $this->headers = new ResponseHeaderBag($headers);

        $this->setContent($content);
        $this->setStatusCode($status);
        $this->setProtocolVersion('1.0');
    }

    /**
     * Set the content on the response.
     *
     * @param  mixed  $content
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function setContent(/*mixed */$content)/*: static*/
    {
        $content = backport_type_check('mixed', $content);

        $this->original = $content;

        // If the content is "JSONable" we will set the appropriate header and convert
        // the content to JSON. This is useful when returning something like models
        // from routes that will be automatically transformed to their JSON form.
        if ($this->shouldBeJson($content)) {
            $this->header('Content-Type', 'application/json');

            $content = $this->morphToJson($content);

            if ($content === false) {
                throw new InvalidArgumentException(json_last_error_msg());
            }
        }

        // If this content implements the "Renderable" interface then we will call the
        // render method on the object so we will avoid any "__toString" exceptions
        // that might be thrown and have their errors obscured by PHP's handling.
        elseif ($content instanceof Renderable) {
            $content = $content->render();
        }

        $content = $this->patchShouldBeJson($content);

        parent::setContent($content);

        return $this;
    }

    /**
     * Determine if the given content should be turned into JSON.
     *
     * @param  mixed  $content
     * @return bool
     */
    protected function shouldBeJson($content)
    {
        return $content instanceof Arrayable ||
               $content instanceof Jsonable ||
               $content instanceof ArrayObject ||
               $content instanceof JsonSerializable ||
               is_array($content);
    }

    /**
     * Morph the given content into JSON.
     *
     * @param  mixed  $content
     * @return string
     */
    protected function morphToJson($content)
    {
        if ($content instanceof Jsonable) {
            return $content->toJson();
        } elseif ($content instanceof Arrayable) {
            return json_encode($content->toArray());
        }

        return json_encode($content);
    }

    protected function patchShouldBeJson($content)
    {
        if ($this->headers->has('content-type') && Str::contains($this->headers->get('content-type'), ['/json', '+json'])) {
            // if ($content === null) {
            //     return "null";
            // }
            if ($content === false) {
                return "false";
            }
            // if ($content === true) {
            //     return "true";
            // }
        }

        return $content;
    }

    protected function ensureIEOverSSLCompatibility(SymfonyRequest $request)/*: void*/
    {
        $contentDisposition = $this->headers->get('Content-Disposition');
        $htpUserAgent = $request->server->get('HTTP_USER_AGENT');

        if (false !== stripos(isset($contentDisposition) ? $contentDisposition : '', 'attachment')
            && 1 == preg_match('/MSIE (.*?);/i', isset($htpUserAgent) ? $htpUserAgent : '', $match) && true === $request->isSecure()) {
            if ((int) preg_replace('/(MSIE )(.*?);/', '$2', $match[0]) < 9) {
                $this->headers->remove('Cache-Control');
            }
        }
    }
}
