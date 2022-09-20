<?php

// declare(strict_types=1);

namespace League\Flysystem\Patch;

use function rtrim;
use function strlen;
use function substr;

final class PathPrefixer
{
    /**
     * @var string
     */
    private $prefix = '';

    /**
     * @var string
     */
    private $separator = '/';

    public function __construct(/*string */$prefix, /*string */$separator = '/')
    {
        $prefix = cast_to_string($prefix);

        $separator = cast_to_string($separator);

        $this->prefix = rtrim($prefix, '\\/');

        if ($this->prefix !== '' || $prefix === $separator) {
            $this->prefix .= $separator;
        }

        $this->separator = $separator;
    }

    public function prefixPath(/*string */$path)/*: string*/
    {
        $path = cast_to_string($path);

        return $this->prefix . ltrim($path, '\\/');
    }

    public function stripPrefix(/*string */$path)/*: string*/
    {
        $path = cast_to_string($path);

        /* @var string */
        return substr($path, strlen($this->prefix));
    }

    public function stripDirectoryPrefix(/*string */$path)/*: string*/
    {
        $path = cast_to_string($path);

        return rtrim($this->stripPrefix($path), '\\/');
    }

    public function prefixDirectoryPath(/*string */$path)/*: string*/
    {
        $path = cast_to_string($path);

        $prefixedPath = $this->prefixPath(rtrim($path, '\\/'));

        if ($prefixedPath === '' || substr($prefixedPath, -1) === $this->separator) {
            return $prefixedPath;
        }

        return $prefixedPath . $this->separator;
    }
}