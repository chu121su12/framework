<?php

namespace Laravel\Octane\Commands;

use Laravel\Octane\RoadRunner\ServerProcessInspector as RoadRunnerServerProcessInspector;
use Laravel\Octane\Swoole\ServerProcessInspector as SwooleServerProcessInspector;

class ReloadCommand extends Command
{
    /**
     * The command's signature.
     *
     * @var string
     */
    public $signature = 'octane:reload {--server= : The server that is running the application}';

    /**
     * The command's description.
     *
     * @var string
     */
    public $description = 'Reload the Octane workers';

    /**
     * Handle the command.
     *
     * @return int
     */
    public function handle()
    {
        $server = $this->option('server') ?: config('octane.server');

        return backport_match ($server,
            ['swoole', function () { return $this->reloadSwooleServer(); }],
            ['roadrunner', function () { return $this->reloadRoadRunnerServer(); }],
            [__BACKPORT_MATCH_DEFAULT_CASE__, function () use ($server) { return $this->invalidServer($server); }]
        );
    }

    /**
     * Reload the Swoole server for Octane.
     *
     * @return int
     */
    protected function reloadSwooleServer()
    {
        $inspector = app(SwooleServerProcessInspector::class);

        if (! $inspector->serverIsRunning()) {
            $this->error('Octane server is not running.');

            return 1;
        }

        $this->info('Reloading workers...');

        $inspector->reloadServer();

        return 0;
    }

    /**
     * Reload the RoadRunner server for Octane.
     *
     * @return int
     */
    protected function reloadRoadRunnerServer()
    {
        $inspector = app(RoadRunnerServerProcessInspector::class);

        if (! $inspector->serverIsRunning()) {
            $this->error('Octane server is not running.');

            return 1;
        }

        $this->info('Reloading workers...');

        $inspector->reloadServer();

        return 0;
    }

    /**
     * Inform the user that the server type is invalid.
     *
     * @param  string  $server
     * @return int
     */
    protected function invalidServer(/*string */$server)
    {
        $server = backport_type_check('string', $server);

        $this->error("Invalid server: {$server}.");

        return 1;
    }
}
