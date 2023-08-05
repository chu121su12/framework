<?php

namespace Spatie\Ignition\Solutions\OpenAi;

use Psr\SimpleCache\CacheInterface;
use Spatie\Ignition\Contracts\HasSolutionsForThrowable;
use Throwable;

class OpenAiSolutionProvider implements HasSolutionsForThrowable
{
    protected /*string */$openAiKey;
    protected /*?CacheInterface */$cache;
    protected /*int */$cacheTtlInSeconds;
    protected /*string|null */$applicationType;
    protected /*string|null */$applicationPath;

    public function __construct(
        /*protected string */$openAiKey,
        /*protected *//*?*/CacheInterface $cache = null,
        /*protected int */$cacheTtlInSeconds = 60 * 60,
        /*protected string|null */$applicationType = null,
        /*protected string|null */$applicationPath = null
    ) {
        $this->openAiKey = backport_type_check('string', $openAiKey);
        $this->cache = backport_type_check([CacheInterface::class, 'null'], $cache);
        $this->cacheTtlInSeconds = backport_type_check('int', $cacheTtlInSeconds);
        $this->applicationType = backport_type_check('string|null', $applicationType);
        $this->applicationPath = backport_type_check('string|null', $applicationPath);

        $this->cache ??= new DummyCache();
    }

    public function canSolve(/*Throwable */$throwable)/*: bool*/
    {
        backport_type_throwable($throwable);

        return true;
    }

    public function getSolutions(/*Throwable */$throwable)/*: array*/
    {
        backport_type_throwable($throwable);

        return [
            new OpenAiSolution(
                $throwable,
                $this->openAiKey,
                $this->cache,
                $this->cacheTtlInSeconds,
                $this->applicationType,
                $this->applicationPath
            ),
        ];
    }

    public function applicationType(/*string */$applicationType)/*: self*/
    {
        $applicationType = backport_type_check('string', $applicationType);

        $this->applicationType = $applicationType;

        return $this;
    }

    public function applicationPath(/*string */$applicationPath)/*: self*/
    {
        $applicationPath = backport_type_check('string', $applicationPath);

        $this->applicationPath = $applicationPath;

        return $this;
    }

    public function useCache(CacheInterface $cache, /*int */$cacheTtlInSeconds = 60 * 60)/*: self*/
    {
        $cacheTtlInSeconds = backport_type_check('int', $cacheTtlInSeconds);

        $this->cache = $cache;

        $this->cacheTtlInSeconds = $cacheTtlInSeconds;

        return $this;
    }
}
