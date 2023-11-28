<?php

namespace Orchestra\Testbench\Foundation\Console\Actions;

use Illuminate\Console\View\Components\Factory as ComponentsFactory;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;

class DeleteFiles extends Action
{
    public /*Filesystem */$filesystem;
    public /*?ComponentsFactory */$components;

    /**
     * Construct a new action instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $filesystem
     * @param  \Illuminate\Console\View\Components\Factory  $components
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
     * @param  iterable<int, string>  $files
     * @return void
     */
    public function handle(/*iterable */$files)/*: void*/
    {
        $files = backport_type_check('iterable', $files);

        LazyCollection::make($files)
            ->reject(static function ($file) {
                return str_ends_with($file, '.gitkeep') || str_ends_with($file, '.gitignore');
            })->each(function ($file) {
                if ($this->filesystem->exists($file)) {
                    $this->filesystem->delete($file);

                    if (isset($this->components)) {
                        $this->components->task(
                            sprintf('File [%s] has been deleted', $this->pathLocation($file))
                        );
                    }
                } else {
                    if (isset($this->components)) {
                        $this->components->twoColumnDetail(
                            sprintf('File [%s] doesn\'t exists', $this->pathLocation($file)),
                            '<fg=yellow;options=bold>SKIPPED</>'
                        );
                    }
                }
            });
    }
}
