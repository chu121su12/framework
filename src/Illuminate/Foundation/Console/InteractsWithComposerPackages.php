<?php

namespace Illuminate\Foundation\Console;

use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

trait InteractsWithComposerPackages
{
    /**
     * Installs the given Composer Packages into the application.
     *
     * @param  string  $composer
     * @param  array  $packages
     * @return bool
     */
    protected function requireComposerPackages(/*string */$composer, array $packages)
    {
        $composer = backport_type_check('string', $composer);

        if ($composer !== 'global') {
            $command = [$this->phpBinary(), $composer, 'require'];
        }

        $command = array_merge(
            isset($command) ? $command : ['composer', 'require'],
            $packages
        );

        return ! (new Process($command, $this->laravel->basePath(), ['COMPOSER_MEMORY_LIMIT' => '-1']))
            ->setTimeout(null)
            ->run(function ($type, $output) {
                $this->output->write($output);
            });
    }

    /**
     * Get the path to the appropriate PHP binary.
     *
     * @return string
     */
    protected function phpBinary()
    {
        return (new PhpExecutableFinder())->find(false) ?: 'php';
    }
}
