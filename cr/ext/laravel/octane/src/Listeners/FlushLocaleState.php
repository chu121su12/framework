<?php

namespace Laravel\Octane\Listeners;

use Carbon\Laravel\ServiceProvider as CarbonServiceProvider;

class FlushLocaleState
{
    /**
     * Handle the event.
     *
     * @param  mixed  $event
     * @return void
     */
    public function handle($event)/*: void*/
    {
        $config = $event->sandbox->make('config');

        tap($event->sandbox->make('translator'), function ($translator) use ($config) {
            $translator->setLocale($config->get('app.locale'));
            $translator->setFallback($config->get('app.fallback_locale'));
        });

        $provider = tap(new CarbonServiceProvider($event->app))->updateLocale();

        collect($event->sandbox->getProviders($provider))
            ->values()
            ->whenNotEmpty(function ($providers) use ($event) {
                return $providers->first()->setAppGetter(function () use ($event) { return $event->sandbox; });
            });
    }
}
