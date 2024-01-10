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

        $majorVersion = LaravelVersion::major();

        if (str_contains($throwable->getMessage(), Collection::class)) {
            return "https://laravel.com/docs/{$majorVersion}.x/collections#available-methods";
        }

        $type = $this->getType($throwable);

        if (! $type) {
            return null;
        }

        switch ($type) {
            case 'Auth': return "https://laravel.com/docs/{$majorVersion}.x/authentication";
            case 'Broadcasting': return "https://laravel.com/docs/{$majorVersion}.x/broadcasting";
            case 'Container': return "https://laravel.com/docs/{$majorVersion}.x/container";
            case 'Database': return "https://laravel.com/docs/{$majorVersion}.x/eloquent";
            case 'Pagination': return "https://laravel.com/docs/{$majorVersion}.x/pagination";
            case 'Queue': return "https://laravel.com/docs/{$majorVersion}.x/queues";
            case 'Routing': return "https://laravel.com/docs/{$majorVersion}.x/routing";
            case 'Session': return "https://laravel.com/docs/{$majorVersion}.x/session";
            case 'Validation': return "https://laravel.com/docs/{$majorVersion}.x/validation";
            case 'View': return "https://laravel.com/docs/{$majorVersion}.x/views";
            default: return null;
        }
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
