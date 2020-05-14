<?php

namespace Orchestra\Testbench\Contracts;

interface Laravel
{
    /**
     * Get application timezone.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return string|null
     */
    public function getApplicationTimezone($app);

    /**
     * Override application bindings.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    public function overrideApplicationBindings($app);

    /**
     * Get application aliases.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    public function getApplicationAliases($app);

    /**
     * Override application aliases.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    public function overrideApplicationAliases($app);

    /**
     * Get package aliases.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    public function getPackageAliases($app);

    /**
     * Get package bootstrapper.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    public function getPackageBootstrappers($app);

    /**
     * Get application providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    public function getApplicationProviders($app);

    /**
     * Override application aliases.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    public function overrideApplicationProviders($app);

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    public function getPackageProviders($app);

    /**
     * Get base path.
     *
     * @return string
     */
    public function getBasePath();

    /**
     * Resolve application core configuration implementation.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return void
     */
    public function resolveApplicationConfiguration($app);

    /**
     * Resolve application core implementation.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return void
     */
    public function resolveApplicationCore($app);

    /**
     * Resolve application Console Kernel implementation.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return void
     */
    public function resolveApplicationConsoleKernel($app);

    /**
     * Resolve application HTTP Kernel implementation.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return void
     */
    public function resolveApplicationHttpKernel($app);

    /**
     * Resolve application HTTP exception handler.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return void
     */
    public function resolveApplicationExceptionHandler($app);
}
