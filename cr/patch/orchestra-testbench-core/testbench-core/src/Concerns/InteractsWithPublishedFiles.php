<?php

namespace Orchestra\Testbench\Concerns;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait InteractsWithPublishedFiles
{
    /**
     * Setup Interacts with Published Files environment.
     */
    protected function setUpInteractsWithPublishedFiles()
    {
        $this->cleanUpFiles();
        $this->cleanUpMigrationFiles();
    }

    /**
     * Teardown Interacts with Published Files environment.
     */
    protected function tearDownInteractsWithPublishedFiles()
    {
        $this->cleanUpFiles();
        $this->cleanUpMigrationFiles();
    }

    /**
     * Assert file does contains data.
     */
    protected function assertFileContains(array $contains, $file, $message = '')
    {
        $file = cast_to_string($file);

        $message = cast_to_string($message);

        $this->assertFilenameExists($file);

        $haystack = $this->app['files']->get(
            $this->app->basePath($file)
        );

        foreach ($contains as $needle) {
            $this->assertStringContainsString($needle, $haystack, $message);
        }
    }

    /**
     * Assert file doesn't contains data.
     */
    protected function assertFileNotContains(array $contains, $file, $message = '')
    {
        $file = cast_to_string($file);

        $message = cast_to_string($message);

        $this->assertFilenameExists($file);

        $haystack = $this->app['files']->get(
            $this->app->basePath($file)
        );

        foreach ($contains as $needle) {
            $this->assertStringNotContainsString($needle, $haystack, $message);
        }
    }

    /**
     * Assert file does contains data.
     */
    protected function assertMigrationFileContains(array $contains, $file, $message = '')
    {
        $file = cast_to_string($file);

        $message = cast_to_string($message);

        $haystack = $this->app['files']->get($this->getMigrationFile($file));

        foreach ($contains as $needle) {
            $this->assertStringContainsString($needle, $haystack, $message);
        }
    }

    /**
     * Assert file doesn't contains data.
     */
    protected function assertMigrationFileNotContains(array $contains, $file, $message = '')
    {
        $file = cast_to_string($file);

        $message = cast_to_string($message);

        $haystack = $this->app['files']->get($this->getMigrationFile($file));

        foreach ($contains as $needle) {
            $this->assertStringNotContainsString($needle, $haystack, $message);
        }
    }

    /**
     * Assert filename exists.
     */
    protected function assertFilenameExists($file)
    {
        $file = cast_to_string($file);

        $appFile = $this->app->basePath($file);

        $this->assertTrue($this->app['files']->exists($appFile), "Assert file {$file} does exist");
    }

    /**
     * Assert filename not exists.
     */
    protected function assertFilenameNotExists($file)
    {
        $file = cast_to_string($file);

        $appFile = $this->app->basePath($file);

        $this->assertTrue(! $this->app['files']->exists($appFile), "Assert file {$file} doesn't exist");
    }

    /**
     * Removes generated files.
     */
    protected function cleanUpFiles()
    {
        $this->app['files']->delete(
            Collection::make(isset($this->files) ? $this->files : [])
                ->transform(function ($file) {
                    return $this->app->basePath($file);
                })
                ->filter(function ($file) {
                    return $this->app['files']->exists($file);
                })->all()
        );
    }

    /**
     * Removes generated migration files.
     */
    protected function getMigrationFile($filename)
    {
        $filename = cast_to_string($filename);

        $migrationPath = $this->app->databasePath('migrations');

        return $this->app['files']->glob("{$migrationPath}/*{$filename}")[0];
    }

    /**
     * Removes generated migration files.
     */
    protected function cleanUpMigrationFiles()
    {
        $this->app['files']->delete(
            Collection::make($this->app['files']->files($this->app->databasePath('migrations')))
                ->filter(function ($file) {
                    return Str::endsWith($file, '.php');
                })->all()
        );
    }
}
