<?php

use CR\LaravelBackport\SymfonyHelper;
use Symfony\Component\Process\Process;

return function ($url) {
    SymfonyHelper::processFromShellCommandline(sprintf('echo %s > %s', escapeshellarg($url.'?expected-query=1'), escapeshellarg($GLOBALS['open-strategy-output-path'])))->run();
};
