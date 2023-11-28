<?php

namespace Orchestra\Testbench\Concerns;

use Illuminate\Support\Collection;

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
        $this->cleanUpPublishedFiles();
        $this->cleanUpPublishedMigrationFiles();

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
            $this->cleanUpPublishedFiles();
            $this->cleanUpPublishedMigrationFiles();
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
    protected function assertFileDoesNotContains(array $contains, /*string */$file, /*string */$message = '')/*: void*/
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
     * Assert file doesn't contains data.
     *
     * @param  array<int, string>  $contains
     */
    protected function assertFileNotContains(array $contains, /*string */$file, /*string */$message = '')/*: void*/
    {
        $file = backport_type_check('string', $file);

        $message = backport_type_check('string', $message);

        $this->assertFileDoesNotContains($contains, $file, $message);
    }

    /**
     * Assert file does contains data.
     *
     * @param  array<int, string>  $contains
     */
    protected function assertMigrationFileContains(array $contains, /*string */$file, /*string */$message = '', /*?string */$directory = null)/*: void*/
    {
        $file = backport_type_check('string', $file);

        $message = backport_type_check('string', $message);

        $directory = backport_type_check('?string', $directory);

        $migrationFile = $this->findFirstPublishedMigrationFile($file, $directory);

        $this->assertTrue(! \is_null($migrationFile), "Assert migration file {$file} does exist");

        $haystack = $this->app['files']->get($migrationFile);

        foreach ($contains as $needle) {
            $this->assertStringContainsString($needle, $haystack, $message);
        }
    }

    /**
     * Assert file doesn't contains data.
     *
     * @param  array<int, string>  $contains
     */
    protected function assertMigrationFileDoesNotContains(array $contains, /*string */$file, /*string */$message = '', /*?string */$directory = null)/*: void*/
    {
        $file = backport_type_check('string', $file);

        $message = backport_type_check('string', $message);

        $directory = backport_type_check('?string', $directory);

        $migrationFile = $this->findFirstPublishedMigrationFile($file, $directory);

        $this->assertTrue(! \is_null($migrationFile), "Assert migration file {$file} does exist");

        $haystack = $this->app['files']->get($migrationFile);

        foreach ($contains as $needle) {
            $this->assertStringNotContainsString($needle, $haystack, $message);
        }
    }

    /**
     * Assert file doesn't contains data.
     *
     * @param  array<int, string>  $contains
     */
    protected function assertMigrationFileNotContains(array $contains, /*string */$file, /*string */$message = '', /*?string */$directory = null)/*: void*/
    {
        $file = backport_type_check('string', $file);

        $message = backport_type_check('string', $message);

        $directory = backport_type_check('?string', $directory);

        $this->assertMigrationFileDoesNotContains($contains, $file, $message, $directory);
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
    protected function assertFilenameDoesNotExists(/*string */$file)/*: void*/
    {
        $file = backport_type_check('string', $file);

        $appFile = $this->app->basePath($file);

        $this->assertTrue(! $this->app['files']->exists($appFile), "Assert file {$file} doesn't exist");
    }

    /**
     * Assert filename not exists.
     */
    protected function assertFilenameNotExists(/*string */$file)/*: void*/
    {
        $file = backport_type_check('string', $file);

        $this->assertFilenameDoesNotExists($file);
    }

    /**
     * Assert migration filename exists.
     */
    protected function assertMigrationFileExists(/*string */$file, /*?string */$directory = null)/*: void*/
    {
        $file = backport_type_check('string', $file);

        $directory = backport_type_check('?string', $directory);

        $migrationFile = $this->findFirstPublishedMigrationFile($file, $directory);

        $this->assertTrue(! \is_null($migrationFile), "Assert migration file {$file} does exist");
    }

    /**
     * Assert migration filename not exists.
     */
    protected function assertMigrationFileDoesNotExists(/*string */$file, /*?string */$directory = null)/*: void*/
    {
        $file = backport_type_check('string', $file);

        $directory = backport_type_check('?string', $directory);

        $migrationFile = $this->findFirstPublishedMigrationFile($file, $directory);

        $this->assertTrue(\is_null($migrationFile), "Assert migration file {$file} doesn't exist");
    }

    /**
     * Assert migration filename not exists.
     */
    protected function assertMigrationFileNotExists(/*string */$file, /*?string */$directory = null)/*: void*/
    {
        $file = backport_type_check('string', $file);

        $directory = backport_type_check('?string', $directory);

        $this->assertMigrationFileNotExists($file, $directory);
    }

    /**
     * Removes generated files.
     */
    protected function cleanUpPublishedFiles()/*: void*/
    {
        $this->app['files']->delete(
            Collection::make(isset($this->files) ? $this->files : [])
                ->transform(function ($file) { return $this->app->basePath($file); })
                ->map(function ($file) { return str_contains($file, '*') ? \array_merge([], $this->app['files']->glob($file)) : $file; })
                ->flatten()
                ->filter(function ($file) { return $this->app['files']->exists($file); })
                ->reject(static function ($file) {
                    return str_ends_with($file, '.gitkeep') || str_ends_with($file, '.gitignore');
                })->all()
        );
    }

    /**
     * Removes generated migration files.
     */
    protected function findFirstPublishedMigrationFile(/*string */$filename, /*?string */$directory = null)/*: ?string*/
    {
        $filename = backport_type_check('string', $filename);

        $directory = backport_type_check('?string', $directory);

        $migrationPath = ! \is_null($directory)
            ? $this->app->basePath($directory)
            : $this->app->databasePath('migrations');

        $glob = $this->app['files']->glob("{$migrationPath}/*{$filename}");

        return isset($glob[0]) ? $glob[0] : null;
    }

    /**
     * Removes generated migration files.
     */
    protected function cleanUpPublishedMigrationFiles()/*: void*/
    {
        $this->app['files']->delete(
            Collection::make($this->app['files']->files($this->app->databasePath('migrations')))
                ->filter(static function ($file) {
                    return str_ends_with($file, '.php');
                })->all()
        );
    }
}
