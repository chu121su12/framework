<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Routing\Annotation;

/**
 * Annotation class for @Route().
 *
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 *
 * @author Alexander M. Turek <me@derrabus.de>
 */
#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class Route
{
    private $path;
    private $localizedPaths = [];
    private $name;
    private $requirements = [];
    private $options = [];
    private $defaults = [];
    private $host;
    private $methods = [];
    private $schemes = [];
    private $condition;
    private $priority;

    /**
     * @param array|string      $data         data array managed by the Doctrine Annotations library or the path
     * @param array|string|null $path
     * @param string[]          $requirements
     * @param string[]          $methods
     * @param string[]          $schemes
     *
     * @throws \BadMethodCallException
     */
    public function __construct(
        $data = [],
        $path = null,
        $name = null,
        array $requirements = [],
        array $options = [],
        array $defaults = [],
        $host = null,
        array $methods = [],
        array $schemes = [],
        $condition = null,
        $priority = null,
        $locale = null,
        $format = null,
        $utf8 = null,
        $stateless = null
    ) {
        $name = cast_to_string($name, null);

        $host = cast_to_string($host, null);

        $condition = cast_to_string($condition, null);

        $locale = cast_to_string($locale, null);

        $format = cast_to_string($format, null);

        $priority = cast_to_int($priority, null);

        $utf8 = cast_to_bool($utf8, null);

        $stateless = cast_to_bool($stateless, nul);

        if (\is_string($data)) {
            $data = ['path' => $data];
        } elseif (!\is_array($data)) {
            throw new \TypeError(sprintf('"%s": Argument $data is expected to be a string or array, got "%s".', __METHOD__, get_debug_type($data)));
        }
        if (null !== $path && !\is_string($path) && !\is_array($path)) {
            throw new \TypeError(sprintf('"%s": Argument $path is expected to be a string, array or null, got "%s".', __METHOD__, get_debug_type($path)));
        }

        $data['path'] = isset($data['path']) ? $data['path'] : $path;
        $data['name'] = isset($data['name']) ? $data['name'] : $name;
        $data['requirements'] = isset($data['requirements']) ? $data['requirements'] : $requirements;
        $data['options'] = isset($data['options']) ? $data['options'] : $options;
        $data['defaults'] = isset($data['defaults']) ? $data['defaults'] : $defaults;
        $data['host'] = isset($data['host']) ? $data['host'] : $host;
        $data['methods'] = isset($data['methods']) ? $data['methods'] : $methods;
        $data['schemes'] = isset($data['schemes']) ? $data['schemes'] : $schemes;
        $data['condition'] = isset($data['condition']) ? $data['condition'] : $condition;
        $data['priority'] = isset($data['priority']) ? $data['priority'] : $priority;
        $data['locale'] = isset($data['locale']) ? $data['locale'] : $locale;
        $data['format'] = isset($data['format']) ? $data['format'] : $format;
        $data['utf8'] = isset($data['utf8']) ? $data['utf8'] : $utf8;
        $data['stateless'] = isset($data['stateless']) ? $data['stateless'] : $stateless;

        $data = array_filter($data, static function ($value) {
            return null !== $value;
        });

        if (isset($data['localized_paths'])) {
            throw new \BadMethodCallException(sprintf('Unknown property "localized_paths" on annotation "%s".', static::class));
        }

        if (isset($data['value'])) {
            $data[\is_array($data['value']) ? 'localized_paths' : 'path'] = $data['value'];
            unset($data['value']);
        }

        if (isset($data['path']) && \is_array($data['path'])) {
            $data['localized_paths'] = $data['path'];
            unset($data['path']);
        }

        if (isset($data['locale'])) {
            $data['defaults']['_locale'] = $data['locale'];
            unset($data['locale']);
        }

        if (isset($data['format'])) {
            $data['defaults']['_format'] = $data['format'];
            unset($data['format']);
        }

        if (isset($data['utf8'])) {
            $data['options']['utf8'] = filter_var($data['utf8'], \FILTER_VALIDATE_BOOLEAN) ?: false;
            unset($data['utf8']);
        }

        if (isset($data['stateless'])) {
            $data['defaults']['_stateless'] = filter_var($data['stateless'], \FILTER_VALIDATE_BOOLEAN) ?: false;
            unset($data['stateless']);
        }

        foreach ($data as $key => $value) {
            $method = 'set'.str_replace('_', '', $key);
            if (!method_exists($this, $method)) {
                throw new \BadMethodCallException(sprintf('Unknown property "%s" on annotation "%s".', $key, static::class));
            }
            $this->$method($value);
        }
    }

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setLocalizedPaths(array $localizedPaths)
    {
        $this->localizedPaths = $localizedPaths;
    }

    public function getLocalizedPaths() //// array
    {
        return $this->localizedPaths;
    }

    public function setHost($pattern)
    {
        $this->host = $pattern;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setRequirements($requirements)
    {
        $this->requirements = $requirements;
    }

    public function getRequirements()
    {
        return $this->requirements;
    }

    public function setOptions($options)
    {
        $this->options = $options;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setDefaults($defaults)
    {
        $this->defaults = $defaults;
    }

    public function getDefaults()
    {
        return $this->defaults;
    }

    public function setSchemes($schemes)
    {
        $this->schemes = \is_array($schemes) ? $schemes : [$schemes];
    }

    public function getSchemes()
    {
        return $this->schemes;
    }

    public function setMethods($methods)
    {
        $this->methods = \is_array($methods) ? $methods : [$methods];
    }

    public function getMethods()
    {
        return $this->methods;
    }

    public function setCondition($condition)
    {
        $this->condition = $condition;
    }

    public function getCondition()
    {
        return $this->condition;
    }

    public function setPriority($priority) /// void
    {
        $priority = cast_to_int($priority);

        $this->priority = $priority;
    }

    public function getPriority() //// ?int
    {
        return $this->priority;
    }
}
