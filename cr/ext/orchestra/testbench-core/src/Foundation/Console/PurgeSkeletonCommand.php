<?php

namespace Orchestra\Testbench\Foundation\Console;

use CR\LaravelBackport\SymfonyHelper;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Orchestra\Testbench\Contracts\Config as ConfigContract;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'package:purge-skeleton', description: 'Purge skeleton folder to original state')]
class PurgeSkeletonCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'package:purge-skeleton';
    protected $description = 'Purge skeleton folder to original state';

    /**
     * Execute the console command.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $filesystem
     * @return int
     */
    public function handle(Filesystem $filesystem, ConfigContract $config)
    {
        $this->call('config:clear');
        $this->call('event:clear');
        $this->call('route:clear');
        $this->call('view:clear');

        $purgeAttributes = $config->getPurgeAttributes();
        $files = $purgeAttributes['files'];
        $directories = $purgeAttributes['directories'];

        $workingPath = $this->laravel->basePath();

        (new Actions\DeleteFiles(
            /*filesystem: */$filesystem,
            /*$components = */null,
            /*workingPath: */$workingPath
        ))->handle(
            Collection::make([
                '.env',
                'testbench.yaml',
            ])->map(function ($file) { return $this->laravel->basePath($file); })
        );

        (new Actions\DeleteFiles(
            /*filesystem: */$filesystem,
            /*$components = */null,
            /*workingPath: */$workingPath
        ))->handle(
            LazyCollection::make(function () use ($filesystem) {
                yield $this->laravel->basePath('database/database.sqlite');
                yield $filesystem->glob($this->laravel->basePath('routes/testbench-*.php'));
                yield $filesystem->glob($this->laravel->basePath('storage/app/public/*'));
                yield $filesystem->glob($this->laravel->basePath('storage/app/*'));
                yield $filesystem->glob($this->laravel->basePath('storage/framework/sessions/*'));
            })->flatten()
        );

        (new Actions\DeleteFiles(
            /*filesystem: */$filesystem,
            /*components: */$this->components,
            /*workingPath: */$workingPath
        ))->handle(
            LazyCollection::make($files)
                ->map(function ($file) { return $this->laravel->basePath($file); })
                ->map(static function ($file) use ($filesystem) {
                    return str_contains($file, '*')
                        ? [...$filesystem->glob($file)]
                        : $file;
                })->flatten()
                ->reject(function ($file) { return str_contains($file, '*'); })
        );

        (new Actions\DeleteDirectories(
            /*filesystem: */$filesystem,
            /*components: */$this->components,
            /*workingPath: */$workingPath
        ))->handle(
            Collection::make($directories)
                ->map(function ($directory) { return $this->laravel->basePath($directory); })
                ->map(static function ($directory) use ($filesystem) {
                    return str_contains($directory, '*')
                        ? \array_merge([]. $filesystem->glob($directory))
                        : $directory;
                })->flatten()
                ->reject(static function ($directory) {
                    return str_contains($directory, '*');
                })
        );

        return SymfonyHelper::CONSOLE_SUCCESS;
    }
}
