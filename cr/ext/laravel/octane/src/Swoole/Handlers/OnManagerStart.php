<?php

namespace Laravel\Octane\Swoole\Handlers;

use Laravel\Octane\Swoole\SwooleExtension;

class OnManagerStart
{
    protected $extension;
    protected $appName;
    protected $shouldSetProcessName;

    public function __construct(
        /*protected */SwooleExtension $extension,
        /*protected string */$appName,
        /*protected bool */$shouldSetProcessName = true
    ) {
        $this->extension = $extension;
        $this->appName = backport_type_check('string', $appName);
        $this->shouldSetProcessName = backport_type_check('bool', $shouldSetProcessName);
    }

    /**
     * Handle the "managerstart" Swoole event.
     *
     * @return void
     */
    public function __invoke()
    {
        if ($this->shouldSetProcessName) {
            $this->extension->setProcessName($this->appName, 'manager process');
        }
    }
}
