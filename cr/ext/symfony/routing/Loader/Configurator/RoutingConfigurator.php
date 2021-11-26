<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Routing\Loader\Configurator;

use Symfony\Component\Routing\Loader\PhpFileLoader;
use Symfony\Component\Routing\RouteCollection;

/**
 * @author Nicolas Grekas <p@tchwork.com>
 */
class RoutingConfigurator
{
    use Traits\AddTrait;

    private $loader;
    private $path;
    private $file;
    private $env;

    public function __construct(RouteCollection $collection, PhpFileLoader $loader, /*string */$path, /*string */$file, /*string */$env = null)
    {
        $file = cast_to_string($file);

        $path = cast_to_string($path);

        $env = cast_to_string($env, null);

        $this->collection = $collection;
        $this->loader = $loader;
        $this->path = $path;
        $this->file = $file;
        $this->env = $env;
    }

    /**
     * @param string|string[]|null $exclude Glob patterns to exclude from the import
     */
    final public function import($resource, $type = null, $ignoreErrors = false, $exclude = null) // ImportConfigurator
    {
        $ignoreErrors = cast_to_bool($ignoreErrors);

        $type = cast_to_string($type, null);

        $this->loader->setCurrentDir(\dirname($this->path));

        $imported = $this->loader->import($resource, $type, $ignoreErrors, $this->file, $exclude) ?: [];
        if (!\is_array($imported)) {
            return new ImportConfigurator($this->collection, $imported);
        }

        $mergedCollection = new RouteCollection();
        foreach ($imported as $subCollection) {
            $mergedCollection->addCollection($subCollection);
        }

        return new ImportConfigurator($this->collection, $mergedCollection);
    }

    final public function collection($name = '') // CollectionConfigurator
    {
        $name = cast_to_string($name);

        return new CollectionConfigurator($this->collection, $name);
    }

    /**
     * Get the current environment to be able to write conditional configuration.
     */
    final public function env()///: ?string
    {
        return $this->env;
    }

    /**
     * @return static
     */
    final public function withPath($path) /// self
    {
        $path = cast_to_string($path);

        $clone = clone $this;
        $clone->path = $clone->file = $path;

        return $clone;
    }
}
