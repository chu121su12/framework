<?php

namespace Orchestra\Testbench\Concerns;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait InteractsWithPublishedFiles
{
    /**
     * Determine if trait teardown has been registered.
     *
     * @var bool
     */
    protected $interactsWithPublishedFilesTeardownRegistered = false;

    /**
     * Setup Interacts with Published Files environment.
     */
    protected function setUpInteractsWithPublishedFiles()/*: void*/
    {
        $this->cleanUpFiles();
        $this->cleanUpMigrationFiles();

        $this->beforeApplicationDestroyed(function () {
            $this->tearDownInteractsWithPublishedFiles();
        });
    }

    /**
     * Teardown Interacts with Published Files environment.
     */
    protected function tearDownInteractsWithPublishedFiles()/*: void*/
    {
        if ($this->interactsWithPublishedFilesTeardownRegistered === false) {
            $this->cleanUpFiles();
            $this->cleanUpMigrationFiles();
        }

        $this->interactsWithPublishedFilesTeardownRegistered = true;
    }

    /**
     * Assert file does contains data.
     *
     * @param  array<int, string>  $contains
     */
    protected function assertFileContains(array $contains, /*string */$file, /*string */$message = '')/*: void*/
    {
        $file = backport_type_check('string', $file);

        $message = backport_type_check('string', $message);

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
     *
     * @param  array<int, string>  $contains
     */
    protected function assertFileNotContains(array $contains, /*string */$file, /*string */$message = '')/*: void*/
    {
        $file = backport_type_check('string', $file);

        $message = backport_type_check('string', $message);

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
     *
     * @param  array<int, string>  $contains
     */
    protected function assertMigrationFileContains(array $contains, /*string */$file, /*string */$message = '')/*: void*/
    {
        $file = backport_type_check('string', $file);

        $message = backport_type_check('string', $message);

        $haystack = $this->app['files']->get($this->getMigrationFile($file));

        foreach ($contains as $needle) {
            $this->assertStringContainsString($needle, $haystack, $message);
        }
    }

    /**
     * Assert file doesn't contains data.
     *
     * @param  array<int, string>  $contains
     */
    protected function assertMigrationFileNotContains(array $contains, /*string */$file, /*string */$message = '')/*: void*/
    {
        $file = backport_type_check('string', $file);

        $message = backport_type_check('string', $message);

        $haystack = $this->app['files']->get($this->getMigrationFile($file));

        foreach ($contains as $needle) {
            $this->assertStringNotContainsString($needle, $haystack, $message);
        }
    }

    /**
     * Assert filename exists.
     */
    protected function assertFilenameExists(/*string */$file)/*: void*/
    {
        $file = backport_type_check('string', $file);

        $appFile = $this->app->basePath($file);

        $this->assertTrue($this->app['files']->exists($appFile), "Assert file {$file} does exist");
    }

    /**
     * Assert filename not exists.
     */
    protected function assertFilenameNotExists(/*string */$file)/*: void*/
    {
        $file = backport_type_check('string', $file);

        $appFile = $this->app->basePath($file);

        $this->assertTrue(! $this->app['files']->exists($appFile), "Assert file {$file} doesn't exist");
    }

    /**
     * Removes generated files.
     */
    protected function cleanUpFiles()/*: void*/
    {
        $this->app['files']->delete(
            Collection::make(isset($this->files) ? $this->files : [])
                ->transform(function ($file) { return $this->app->basePath($file); })
                ->filter(function ($file) { return $this->app['files']->exists($file); })
                ->all()
        );
    }

    /**
     * Removes generated migration files.
     */
    protected function getMigrationFile(/*string */$filename)/*: string*/
    {
        $filename = backport_type_check('string', $filename);

        $migrationPath = $this->app->databasePath('migrations');

        return $this->app['files']->glob("{$migrationPath}/*{$filename}")[0];
    }

    /**
     * Removes generated migration files.
     */
    protected function cleanUpMigrationFiles()/*: void*/
    {
        $this->app['files']->delete(
            Collection::make($this->app['files']->files($this->app->databasePath('migrations')))
                ->filter(function ($file) { return Str::endsWith($file, '.php'); })
                ->all()
        );
    }
}
