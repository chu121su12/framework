<?php

namespace Orchestra\Testbench\Bootstrap;

use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Orchestra\Testbench\Workbench\Workbench;
use Symfony\Component\Finder\Finder;

use function Orchestra\Testbench\workbench_path;

/**
 * @internal
 */
class LoadConfigurationWithWorkbench extends LoadConfiguration
{
    /**
     * Determine if workbench config file should be loaded.
     *
     * @var bool
     */
    protected $usesWorkbenchConfigFile = false;

    /**
     * Construct a new bootstrap class.
     */
    public function __construct()
    {
        $attributes = Workbench::configuration()->getWorkbenchDiscoversAttributes();

        $this->usesWorkbenchConfigFile = (isset($attributes) && isset($attributes['config']) ? $attributes['config'] : false)
            && is_dir(workbench_path('config'));
    }

    /**
     * Resolve the configuration file.
     *
     * @param  string  $path
     * @param  string  $key
     * @return string
     */
    protected function resolveConfigurationFile(/*string */$path, /*string */$key)/*: string*/
    {
        $key = backport_type_check('string', $key);
        $path = backport_type_check('string', $path);
    
        return $this->usesWorkbenchConfigFile === true && is_file(workbench_path("config/{$key}.php"))
            ? workbench_path("config/{$key}.php")
            : $path;
    }

    /**
     * Extend the loaded configuration.
     *
     * @param  \Illuminate\Support\Collection  $configurations
     * @return \Illuminate\Support\Collection
     */
    protected function extendsLoadedConfiguration(Collection $configurations)/*: Collection*/
    {
        if ($this->usesWorkbenchConfigFile === false) {
            return $configurations;
        }

        LazyCollection::make(static function () {
            foreach (Finder::create()->files()->name('*.php')->in(workbench_path('config')) as $file) {
                yield basename($file->getRealPath(), '.php') => $file->getRealPath();
            }
        })->reject(static function ($path, $key) use ($configurations) {
            return $configurations->has($key);
        })->each(static function ($path, $key) use ($configurations) {
            $configurations->put($key, $path);
        });

        return $configurations;
    }
}
