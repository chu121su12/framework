<?php

namespace Illuminate\Log\Context;

use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Queue;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\ServiceProvider;

class ContextServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->scoped(Repository::class);
    }

    /**
     * Boot the application services.
     *
     * @return void
     */
    public function boot()
    {
        Queue::createPayloadUsing(function ($connection, $queue, $payload) {
            $context = Context::dehydrate();

            return $context === null ? $payload : \array_merge(
                $payload,
                ['illuminate:log:context' => $context]
            );
        });

        $this->app['events']->listen(function (JobProcessing $event) {
            $payload = $event->job->payload();

            Context::hydrate(isset($payload['illuminate:log:context']) ? $payload['illuminate:log:context'] : null);
        });
    }
}
