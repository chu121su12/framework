<?php

use Illuminate\Support\Env;
use Orchestra\Testbench\Foundation\Application;
use Orchestra\Testbench\Foundation\Config;
use Orchestra\Testbench\Workbench\Workbench;

/**
 * Create Laravel application.
 *
 * @param  string  $workingPath
 * @return \Illuminate\Foundation\Application
 */
$createApp = static function (/*string */$workingPath) {
    $workingPath = backport_type_check('string', $workingPath);

    $config = Config::loadFromYaml(
        defined('TESTBENCH_WORKING_PATH') ? TESTBENCH_WORKING_PATH : $workingPath
    );

    $hasEnvironmentFile = ! is_null($config['laravel'])
        ? file_exists($config['laravel'].'/.env')
        : file_exists("{$workingPath}/.env");

    return Application::create(
        /*basePath: */$config['laravel'],
        /*resolvingCallback: */static function ($app) use ($config) {
            Workbench::startWithProviders($app, $config);
            Workbench::discoverRoutes($app, $config);
        },
        /*options: */['load_environment_variables' => $hasEnvironmentFile, 'extra' => $config->getExtraAttributes()]
    );
};

if (! defined('TESTBENCH_WORKING_PATH') && ! is_null(Env::get('TESTBENCH_WORKING_PATH'))) {
    define('TESTBENCH_WORKING_PATH', Env::get('TESTBENCH_WORKING_PATH'));
}

$app = $createApp(realpath(__DIR__.'/../'));

unset($createApp);

/** @var \Illuminate\Routing\Router $router */
$router = $app->make('router');

collect(glob(__DIR__.'/../routes/testbench-*.php'))
    ->each(static function ($routeFile) use ($app, $router) {
        require $routeFile;
    });

return $app;
