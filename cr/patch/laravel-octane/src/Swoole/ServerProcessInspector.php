<?php

namespace Laravel\Octane\Swoole;

use Laravel\Octane\Exec;

class ServerProcessInspector
{
    protected $dispatcher;
    protected $serverStateFile;
    protected $exec;

    public function __construct(
        /*protected */SignalDispatcher $dispatcher,
        /*protected */ServerStateFile $serverStateFile,
        /*protected */Exec $exec
    ) {
        $this->dispatcher = $dispatcher;
        $this->serverStateFile = $serverStateFile;
        $this->exec = $exec;
    }

    /**
     * Determine if the Swoole server process is running.
     *
     * @return bool
     */
    public function serverIsRunning() ////: bool
    {
        $serverStateFile = $this->serverStateFile->read();

        $masterProcessId = $serverStateFile['masterProcessId'];

        $managerProcessId = $serverStateFile['managerProcessId'];

        return $managerProcessId
                ? $masterProcessId && $managerProcessId && $this->dispatcher->canCommunicateWith((int) $managerProcessId)
                : $masterProcessId && $this->dispatcher->canCommunicateWith((int) $masterProcessId);
    }

    /**
     * Reload the Swoole workers.
     *
     * @return void
     */
    public function reloadServer() ////: void
    {
        $serverStateFile = $this->serverStateFile->read();

        $masterProcessId = $serverStateFile['masterProcessId'];

        $this->dispatcher->signal($masterProcessId, SIGUSR1);
    }

    /**
     * Stop the Swoole server.
     *
     * @return bool
     */
    public function stopServer() ////: bool
    {
        $serverStateFile = $this->serverStateFile->read();

        $masterProcessId = $serverStateFile['masterProcessId'];

        $managerProcessId = $serverStateFile['managerProcessId'];

        $workerProcessIds = $this->exec->run('pgrep -P '.$managerProcessId);

        foreach (array_merge([$masterProcessId], [$managerProcessId], $workerProcessIds) as $processId) {
            $this->dispatcher->signal($processId, SIGKILL);
        }

        return true;
    }
}
