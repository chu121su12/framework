<?php

namespace Laravel\Octane\RoadRunner;

use Laravel\Octane\PosixExtension;
use Laravel\Octane\RoadRunner\Concerns\FindsRoadRunnerBinary;
use Laravel\Octane\SymfonyProcessFactory;
use RuntimeException;
use Symfony\Component\Process\Process;

class ServerProcessInspector
{
    use FindsRoadRunnerBinary;

    protected $serverStateFile;
    protected $processFactory;
    protected $posix;

    public function __construct(
        /*protected */ServerStateFile $serverStateFile,
        /*protected */SymfonyProcessFactory $processFactory,
        /*protected */PosixExtension $posix
    ) {
        $this->serverStateFile = $serverStateFile;
        $this->processFactory = $processFactory;
        $this->posix = $posix;
    }

    /**
     * Determine if the RoadRunner server process is running.
     *
     * @return bool
     */
    public function serverIsRunning() ////: bool
    {
        $serverStateFile = $this->serverStateFile->read();

        $masterProcessId = $serverStateFile['masterProcessId'];

        return $masterProcessId && $this->posix->kill($masterProcessId, 0);
    }

    /**
     * Reload the RoadRunner workers.
     *
     * @return void
     */
    public function reloadServer() ////: void
    {
        $serverStateFile = $this->serverStateFile->read();

        $host = $serverStateFile['state']['host'];

        $rpcPort = $serverStateFile['state']['rpcPort'];

        tap($this->processFactory->createProcess([
            $this->findRoadRunnerBinary(),
            'reset',
            '-o', "rpc.listen=tcp://$host:$rpcPort",
            '-s',
        ], base_path()))->start()->waitUntil(function ($type, $buffer) {
            if ($type === Process::ERR) {
                throw new RuntimeException('Cannot reload RoadRunner: '.$buffer);
            }

            return true;
        });
    }

    /**
     * Stop the RoadRunner server.
     *
     * @return bool
     */
    public function stopServer() ////: bool
    {
        $serverStateFile = $this->serverStateFile->read();

        $masterProcessId = $serverStateFile['masterProcessId'];

        return (bool) $this->posix->kill($masterProcessId, SIGTERM);
    }
}
