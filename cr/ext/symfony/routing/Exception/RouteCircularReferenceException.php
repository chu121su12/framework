<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Routing\Exception;

class RouteCircularReferenceException extends RuntimeException
{
    public function __construct(/*string */$routeId, array $path)
    {
        $routeId = cast_to_string($routeId);

        parent::__construct(sprintf('Circular reference detected for route "%s", path: "%s".', $routeId, implode(' -> ', $path)));
    }
}
