<?php

declare(strict_types=1);

namespace Carbon\PHPStan;

use Closure;
use PHPStan\Reflection\Php\BuiltinMethodReflection;
use PHPStan\TrinaryLogic;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionType;
use stdClass;
use Throwable;

final class Macro implements BuiltinMethodReflection
{
    /**
     * The class name.
     *
     * @var class-string
     */
    private $className;

    /**
     * The method name.
     *
     * @var string
     */
    private $methodName;

    /**
     * The reflection function/method.
     *
     * @var ReflectionFunction|ReflectionMethod
     */
    private $reflectionFunction;

    /**
     * The parameters.
     *
     * @var ReflectionParameter[]
     */
    private $parameters;

    /**
     * The is static.
     *
     * @var bool
     */
    private $static = false;

    /**
     * Macro constructor.
     *
     * @param string $className
     * @phpstan-param class-string $className
     *
     * @param string   $methodName
     * @param callable $macro
     */
    public function __construct($className, $methodName, $macro)
    {
        $className = cast_to_string($className);
        $methodName = cast_to_string($methodName);

        $this->className = $className;
        $this->methodName = $methodName;
        $this->reflectionFunction = is_array($macro)
            ? new ReflectionMethod($macro[0], $macro[1])
            : new ReflectionFunction($macro);
        $this->parameters = $this->reflectionFunction->getParameters();

        if ($this->reflectionFunction->isClosure()) {
            try {
                /** @var Closure $closure */
                $closure = $this->reflectionFunction->getClosure();
                $boundClosure = Closure::bind($closure, new stdClass);
                $this->static = (!$boundClosure || (new ReflectionFunction($boundClosure))->getClosureThis() === null);
            } catch (Throwable $e) {
                $this->static = true;
            } catch (\Error $e) {
                $this->static = true;
            } catch (\Exception $e) {
                $this->static = true;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDeclaringClass()
    {
        return new ReflectionClass($this->className);
    }

    /**
     * {@inheritdoc}
     */
    public function isPrivate()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isPublic()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isFinal()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isInternal()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isAbstract()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isStatic()
    {
        return $this->static;
    }

    /**
     * {@inheritdoc}
     */
    public function getDocComment()
    {
        return $this->reflectionFunction->getDocComment() ?: null;
    }

    /**
     * {@inheritdoc}
     */
    public function getFileName()
    {
        return $this->reflectionFunction->getFileName();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->methodName;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getReturnType(): ?ReflectionType
    {
        return $this->reflectionFunction->getReturnType();
    }

    /**
     * {@inheritdoc}
     */
    public function getStartLine()
    {
        return $this->reflectionFunction->getStartLine();
    }

    /**
     * {@inheritdoc}
     */
    public function getEndLine()
    {
        return $this->reflectionFunction->getEndLine();
    }

    /**
     * {@inheritdoc}
     */
    public function isDeprecated()
    {
        return TrinaryLogic::createFromBoolean(
            $this->reflectionFunction->isDeprecated() ||
            preg_match('/@deprecated/i', $this->getDocComment() ?: '')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isVariadic()
    {
        return $this->reflectionFunction->isVariadic();
    }

    /**
     * {@inheritdoc}
     */
    public function getPrototype()
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getReflection(): ?ReflectionMethod
    {
        return $this->reflectionFunction instanceof ReflectionMethod
            ? $this->reflectionFunction
            : null;
    }
}
