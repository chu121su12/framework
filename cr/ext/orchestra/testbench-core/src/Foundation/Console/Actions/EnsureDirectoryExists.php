<?php

namespace Orchestra\Testbench\Foundation\Console\Actions;

use Illuminate\Console\View\Components\Factory as ComponentsFactory;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\LazyCollection;

class EnsureDirectoryExists extends Action
{
    public /*Filesystem */$filesystem;
    public /*?ComponentsFactory */$components;

    /**
     * Construct a new ensure directory exists instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $filesystem
     * @param  \Illuminate\Console\View\Components\Factory|null  $components
     * @param  string|null  $workingPath
     */
    public function __construct(
        /*public */Filesystem $filesystem,
        /*public *//*?*/ComponentsFactory $components = null,
        /*?string */$workingPath = null
    ) {
        $this->filesystem = $filesystem;
        $this->components = $components;
        $workingPath = backport_type_check('?string', $workingPath);

        $this->workingPath = $workingPath;
    }

    /**
     * Handle the action.
     *
     * @param  iterable<int, string>  $directories
     * @return void
     */
    public function handle(/*iterable */$directories)/*: void*/
    {
        $directories = backport_type_check('iterable', $directories);
    
        LazyCollection::make($directories)
            ->each(function ($directory) {
                if ($this->filesystem->isDirectory($directory)) {
                    if (isset($this->components)) {
                        $this->components->twoColumnDetail(
                            sprintf('Directory [%s] already exists', $this->pathLocation($directory)),
                            '<fg=yellow;options=bold>SKIPPED</>'
                        );
                    }

                    return;
                }

                $this->filesystem->ensureDirectoryExists($directory, 0755, true);
                $this->filesystem->copy((string) realpath(__DIR__.'/stubs/.gitkeep'), "{$directory}/.gitkeep");

                if (isset($this->components)) {
                    $this->components->task(sprintf('Prepare [%s] directory', $this->pathLocation($directory)));
                }
            });
    }
}
