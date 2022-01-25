<?php

namespace Spatie\LaravelIgnition\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\LaravelIgnition\Exceptions\ViewException;
use Throwable;

class LaravelDocumentationLinkFinder
{
    public function findLinkForThrowable(/*Throwable */$throwable)/*: ?string*/
    {
        backport_type_throwable($throwable);

        if ($throwable instanceof ViewException) {
            $throwable = $throwable->getPrevious();
        }

        $majorVersion = explode('.', app()->version())[0];

        if (str_contains($throwable->getMessage(), Collection::class)) {
            return "https://laravel.com/docs/{$majorVersion}.x/collections#available-methods";
        }

        $type = $this->getType($throwable);

        if (! $type) {
            return null;
        }

        return backport_match ($type,
            ['Auth', "https://laravel.com/docs/{$majorVersion}.x/authentication"],
            ['Broadcasting', "https://laravel.com/docs/{$majorVersion}.x/broadcasting"],
            ['Container', "https://laravel.com/docs/{$majorVersion}.x/container"],
            ['Database', "https://laravel.com/docs/{$majorVersion}.x/eloquent"],
            ['Pagination', "https://laravel.com/docs/{$majorVersion}.x/pagination"],
            ['Queue', "https://laravel.com/docs/{$majorVersion}.x/queues"],
            ['Routing', "https://laravel.com/docs/{$majorVersion}.x/routing"],
            ['Session', "https://laravel.com/docs/{$majorVersion}.x/session"],
            ['Validation', "https://laravel.com/docs/{$majorVersion}.x/validation"],
            ['View', "https://laravel.com/docs/{$majorVersion}.x/views"],
            [__BACKPORT_MATCH_DEFAULT_CASE__, null]
        );
    }

    protected function getType(/*?Throwable */$throwable = null)/*: ?string*/
    {
        backport_type_throwable($throwable, null);

        if (! $throwable) {
            return null;
        }

        if (str_contains(get_class($throwable), 'Illuminate')) {
            return Str::between(get_class($throwable), 'Illuminate\\', '\\');
        }

        if (str_contains($throwable->getMessage(), 'Illuminate')) {
            return explode('\\', Str::between($throwable->getMessage(), 'Illuminate\\', '\\'))[0];
        }

        return null;
    }
}
