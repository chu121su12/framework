<?php

namespace Laravel\Octane\Commands\Concerns;

use InvalidArgumentException;
use Laravel\Octane\Exceptions\ServerShutdownException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

trait InteractsWithServers
{
    /**
     * Run the given server process.
     *
     * @param  \Symfony\Component\Process\Process  $server
     * @param  \Laravel\Octane\Contracts\ServerProcessInspector  $inspector
     * @param  string  $type
     * @return int
     */
    protected function runServer($server, $inspector, $type)
    {
        while (! $server->isStarted()) {
            sleep(1);
        }

        $this->writeServerRunningMessage();

        $watcher = $this->startServerWatcher();

        try {
            $configUsleepBetweenIterations = config('octane.usleep_between_writing_server_output');
            $usleepBetweenIterations = isset($configUsleepBetweenIterations) ? $configUsleepBetweenIterations : (
                isset($_ENV['LARAVEL_OCTANE_USLEEP_BETWEEN_WRITING_SERVER_OUTPUT']) ? $_ENV['LARAVEL_OCTANE_USLEEP_BETWEEN_WRITING_SERVER_OUTPUT'] :
                (10 * 1000));

            while ($server->isRunning()) {
                $this->writeServerOutput($server);

                if ($watcher->isRunning() &&
                    $watcher->getIncrementalOutput()) {
                    $this->info('Application change detected. Restarting workers…');

                    $inspector->reloadServer();
                } elseif ($watcher->isTerminated()) {
                    $this->error(
                        'Watcher process has terminated. Please ensure Node and chokidar are installed.'.PHP_EOL.
                        $watcher->getErrorOutput()
                    );

                    return 1;
                }

                usleep($usleepBetweenIterations);
            }

            $this->writeServerOutput($server);
        } catch (ServerShutdownException $_e) {
            return 1;
        } finally {
            $this->stopServer();
        }

        return $server->getExitCode();
    }

    /**
     * Start the watcher process for the server.
     *
     * @return \Symfony\Component\Process\Process|object
     */
    protected function startServerWatcher()
    {
        if (! $this->option('watch')) {
            return new NoOpEmptyWatchCallable;
        }

        if (empty($paths = config('octane.watch'))) {
            throw new InvalidArgumentException(
                'List of directories/files to watch not found. Please update your "config/octane.php" configuration file.'
            );
        }

        return tap(new Process([
            (new ExecutableFinder)->find('node'),
            'file-watcher.cjs',
            json_encode(collect(config('octane.watch'))->map(function ($path) { return base_path($path); })),
            $this->option('poll'),
        ], realpath(__DIR__.'/../../../bin'), null, null, null))->start();
    }

    /**
     * Write the server start "message" to the console.
     *
     * @return void
     */
    protected function writeServerRunningMessage()
    {
        $this->info('Server running…');

        $this->output->writeln([
            '',
            '  Local: <fg=white;options=bold>'.($this->hasOption('https') && $this->option('https') ? 'https://' : 'http://').$this->getHost().':'.$this->getPort().' </>',
            '',
            '  <fg=yellow>Press Ctrl+C to stop the server</>',
            '',
        ]);
    }

    /**
     * Retrieve the given server output and flush it.
     *
     * @return array
     */
    protected function getServerOutput($server)
    {
        $output = [
            $server->getIncrementalOutput(),
            $server->getIncrementalErrorOutput(),
        ];

        $server->clearOutput()->clearErrorOutput();

        return $output;
    }

    /**
     * Get the Octane HTTP server host IP to bind on.
     *
     * @return string
     */
    protected function getHost()
    {
        $value = $this->option('host');
        if (isset($value)) {
            return $value;
        }

        $value = config('octane.host');
        if (isset($value)) {
            return $value;
        }

        return isset($_ENV['OCTANE_HOST']) ? $_ENV['OCTANE_HOST'] : '127.0.0.1';
    }

    /**
     * Get the Octane HTTP server port.
     *
     * @return string
     */
    protected function getPort()
    {
        $value = $this->option('port');
        if (isset($value)) {
            return $value;
        }

        $value = config('octane.port');
        if (isset($value)) {
            return $value;
        }

        return isset($_ENV['OCTANE_PORT']) ? $_ENV['OCTANE_PORT'] : '8000';
    }

    /**
     * Ensure the Octane HTTP server port is available.
     */
    protected function ensurePortIsAvailable()/*: void*/
    {
        $host = $this->getHost();

        $port = $this->getPort();

        $connection = @fsockopen($host, $port);

        if (is_resource($connection)) {
            @fclose($connection);

            throw new InvalidArgumentException("Unable to start server. Port {$port} is already in use.");
        }
    }

    /**
     * Returns the list of signals to subscribe.
     */
    public function getSubscribedSignals()/*: array*/
    {
        return [SIGINT, SIGTERM, SIGHUP];
    }

    /**
     * The method will be called when the application is signaled.
     */
    public function handleSignal(/*int */$signal, /*int|false */$previousExitCode = 0)/*: int|false*/
    {
        $signal = backport_type_check('int', $signal);

        $previousExitCode = backport_type_check('int|false', $previousExitCode);

        $this->stopServer();

        exit(0);
    }
}
