<?php

namespace Laravel\Octane\FrankenPhp;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Laravel\Octane\Contracts\ServerProcessInspector as ServerProcessInspectorContract;
use Symfony\Component\Process\Process;

class ServerProcessInspector implements ServerProcessInspectorContract
{
    protected $serverStateFile;

    /**
     * Create a new server process inspector instance.
     */
    public function __construct(
        /*protected */ServerStateFile $serverStateFile
    ) {
        $this->serverStateFile = $serverStateFile;
    }

    /**
     * Determine if the FrankenPHP server process is running.
     */
    public function serverIsRunning()/*: bool*/
    {
        $serverStateFile = $this->serverStateFile->read();

        if (is_null(isset($serverStateFile['masterProcessId']) ? $serverStateFile['masterProcessId'] : null)) {
            return false;
        }

        try {
            return Http::get($this->adminConfigUrl())->successful();
        } catch (ConnectionException $_e) {
            return false;
        }
    }

    /**
     * Reload the FrankenPHP workers.
     */
    public function reloadServer()/*: void*/
    {
        try {
            Http::withBody(Http::get($this->adminConfigUrl())->body(), 'application/json')
                ->withHeaders(['Cache-Control' => 'must-revalidate'])
                ->patch($this->adminConfigUrl());
        } catch (ConnectionException $_e) {
            //
        }
    }

    /**
     * Stop the FrankenPHP server.
     */
    public function stopServer()/*: bool*/
    {
        try {
            return Http::post($this->adminUrl().'/stop')->successful();
        } catch (ConnectionException $_e) {
            return false;
        }
    }

    /**
     * Get the URL to the FrankenPHP admin panel.
     */
    protected function adminUrl()/*: string*/
    {
        $serverStateFile = $this->serverStateFile->read();

        $adminPort = isset($serverStateFile) && isset($serverStateFile['state']) && isset($serverStateFile['state']['adminPort']) ? $serverStateFile['state']['adminPort'] : 2019;

        return "http://localhost:{$adminPort}";
    }

    /**
     * Get the URL to the FrankenPHP admin panel's configuration endpoint.
     */
    protected function adminConfigUrl()/*: string*/
    {
        return "{$this->adminUrl()}/config/apps/frankenphp";
    }
}
