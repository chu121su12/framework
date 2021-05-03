<?php

namespace Laravel\Octane\Swoole\Handlers;

use Laravel\Octane\Swoole\Actions\EnsureRequestsDontExceedMaxExecutionTime;
use Laravel\Octane\Swoole\ServerStateFile;
use Laravel\Octane\Swoole\SwooleExtension;

class OnServerStart
{
    protected $serverStateFile;
    protected $extension;
    protected $appName;
    protected $maxExecutionTime;
    protected $timerTable;
    protected $shouldTick;
    protected $shouldSetProcessName;

    public function __construct(
        /*protected */ServerStateFile $serverStateFile,
        /*protected */SwooleExtension $extension,
        /*protected string */$appName,
        /*protected int */$maxExecutionTime,
        /*protected */$timerTable,
        /*protected bool */$shouldTick = true,
        /*protected bool */$shouldSetProcessName = true
    ) {
        $this->serverStateFile = $serverStateFile;
        $this->extension = $extension;
        $this->appName = cast_to_string($appName);
        $this->maxExecutionTime = cast_to_int($maxExecutionTime);
        $this->timerTable = $timerTable;
        $this->shouldTick = cast_to_bool($shouldTick);
        $this->shouldSetProcessName = cast_to_bool($shouldSetProcessName);
    }

    /**
     * Handle the "start" Swoole event.
     *
     * @param  \Swoole\Http\Server  $server
     * @return void
     */
    public function __invoke($server)
    {
        $this->serverStateFile->writeProcessIds(
            $server->master_pid,
            $server->manager_pid
        );

        if ($this->shouldSetProcessName) {
            $this->extension->setProcessName($this->appName, 'master process');
        }

        if ($this->shouldTick) {
            $server->tick(1000, function () use ($server) {
                $server->task('octane-tick');
            });
        }

        if ($this->maxExecutionTime > 0) {
            $server->tick(1000, function () {
                $ensureRequestsDontExceedMaxExecutionTime = new EnsureRequestsDontExceedMaxExecutionTime(
                    $this->extension, $this->timerTable, $this->maxExecutionTime
                );

                $ensureRequestsDontExceedMaxExecutionTime();
            });
        }
    }
}
