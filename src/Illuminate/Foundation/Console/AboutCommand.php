<?php

namespace Illuminate\Foundation\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Composer;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'about')]
class AboutCommand extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'about {--only= : The section to display}
                {--json : Output the information as JSON}';

    /**
     * The name of the console command.
     *
     * This name is used to identify the command during lazy loading.
     *
     * @var string|null
     *
     * @deprecated
     */
    protected static $defaultName = 'about';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display basic information about your application';

    /**
     * The Composer instance.
     *
     * @var \Illuminate\Support\Composer
     */
    protected $composer;

    /**
     * The data to display.
     *
     * @var array
     */
    protected static $data = [];

    /**
     * The registered callables that add custom data to the command output.
     *
     * @var array
     */
    protected static $customDataResolvers = [];

    /**
     * Create a new command instance.
     *
     * @param  \Illuminate\Support\Composer  $composer
     * @return void
     */
    public function __construct(Composer $composer)
    {
        parent::__construct();

        $this->composer = $composer;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->gatherApplicationInformation();

        collect(static::$data)
            ->map(function ($items) {
                return collect($items)
                    ->map(function ($value) {
                        if (is_array($value)) {
                            return [$value];
                        }

                        if (is_string($value)) {
                            $value = $this->laravel->make($value);
                        }

                        return collect($this->laravel->call($value))
                            ->map(function ($value, $key) { return [$key, $value]; })
                            ->values()
                            ->all();
                    })->flatten(1);
            })
            ->sortBy(function ($data, $key) {
                $index = array_search($key, ['Environment', 'Cache', 'Drivers']);

                return $index === false ? 99 : $index;
            })
            ->filter(function ($data, $key) {
                return $this->option('only') ? in_array(Str::of($key)->lower()->snake(), $this->sections()) : true;
            })
            ->pipe(function ($data) { return $this->display($data); });

        $this->newLine();

        return 0;
    }

    /**
     * Display the application information.
     *
     * @param  \Illuminate\Support\Collection  $data
     * @return void
     */
    protected function display($data)
    {
        $this->option('json') ? $this->displayJson($data) : $this->displayDetail($data);
    }

    /**
     * Display the application information as a detail view.
     *
     * @param  \Illuminate\Support\Collection  $data
     * @return void
     */
    protected function displayDetail($data)
    {
        $data->each(function ($data, $section) {
            $this->newLine();

            $this->components->twoColumnDetail('  <fg=green;options=bold>'.$section.'</>');

            $data
                ->pipe(function ($data) use ($section) {
                    return $section !== 'Environment' ? $data->sort() : $data;
                })
                ->each(function ($detail) {
                    list($label, $value) = $detail;

                    $this->components->twoColumnDetail($label, value($value));
                });
        });
    }

    /**
     * Display the application information as JSON.
     *
     * @param  \Illuminate\Support\Collection  $data
     * @return void
     */
    protected function displayJson($data)
    {
        $output = $data->flatMap(function ($data, $section) {
            return [(string) Str::of($section)->snake() => $data->mapWithKeys(function ($item, $key) {
                return [(string) Str::of($item[0])->lower()->snake() => value($item[1])];
            })];
        });

        $this->output->writeln(strip_tags(json_encode($output)));
    }

    /**
     * Gather information about the application.
     *
     * @return void
     */
    protected function gatherApplicationInformation()
    {
        static::addToSection('Environment', function () {
            $composerVersion = $this->composer->getVersion();

            return [
                'Application Name' => config('app.name'),
                'Laravel Version' => $this->laravel->version(),
                'PHP Version' => phpversion(),
                'Composer Version' => isset($composerVersion) ? $composerVersion : '<fg=yellow;options=bold>-</>',
                'Environment' => $this->laravel->environment(),
                'Debug Mode' => config('app.debug') ? '<fg=yellow;options=bold>ENABLED</>' : 'OFF',
                'URL' => Str::of(config('app.url'))->replace(['http://', 'https://'], ''),
                'Maintenance Mode' => $this->laravel->isDownForMaintenance() ? '<fg=yellow;options=bold>ENABLED</>' : 'OFF',
            ];
        });

        static::addToSection('Cache', function () { return [
            'Config' => $this->laravel->configurationIsCached() ? '<fg=green;options=bold>CACHED</>' : '<fg=yellow;options=bold>NOT CACHED</>',
            'Events' => $this->laravel->eventsAreCached() ? '<fg=green;options=bold>CACHED</>' : '<fg=yellow;options=bold>NOT CACHED</>',
            'Routes' => $this->laravel->routesAreCached() ? '<fg=green;options=bold>CACHED</>' : '<fg=yellow;options=bold>NOT CACHED</>',
            'Views' => $this->hasPhpFiles($this->laravel->storagePath('framework/views')) ? '<fg=green;options=bold>CACHED</>' : '<fg=yellow;options=bold>NOT CACHED</>',
        ]; });

        $logChannel = config('logging.default');

        if (config('logging.channels.'.$logChannel.'.driver') === 'stack') {
            $secondary = collect(config('logging.channels.'.$logChannel.'.channels'))
                ->implode(', ');

            $logs = '<fg=yellow;options=bold>'.$logChannel.'</> <fg=gray;options=bold>/</> '.$secondary;
        } else {
            $logs = $logChannel;
        }

        static::addToSection('Drivers', function () use ($logs) { return array_filter([
            'Broadcasting' => config('broadcasting.default'),
            'Cache' => config('cache.default'),
            'Database' => config('database.default'),
            'Logs' => $logs,
            'Mail' => config('mail.default'),
            'Octane' => config('octane.server'),
            'Queue' => config('queue.default'),
            'Scout' => config('scout.driver'),
            'Session' => config('session.driver'),
        ]); });

        collect(static::$customDataResolvers)->each->__invoke();
    }

    /**
     * Determine whether the given directory has PHP files.
     *
     * @param  string  $path
     * @return bool
     */
    protected function hasPhpFiles(/*string */$path)/*: bool*/
    {
        $path = backport_type_check('string', $path);

        return count(glob($path.'/*.php')) > 0;
    }

    /**
     * Add additional data to the output of the "about" command.
     *
     * @param  string  $section
     * @param  callable|string|array  $data
     * @param  string|null  $value
     * @return void
     */
    public static function add(/*string */$section, $data, /*string */$value = null)
    {
        $section = backport_type_check('string', $section);

        $path = backport_type_check('?string', $path);

        static::$customDataResolvers[] = function () use ($section, $data, $value) {
            return static::addToSection($section, $data, $value);
        };
    }

    /**
     * Add additional data to the output of the "about" command.
     *
     * @param  string  $section
     * @param  callable|string|array  $data
     * @param  string|null  $value
     * @return void
     */
    protected static function addToSection(/*string */$section, $data, /*string */$value = null)
    {
        $section = backport_type_check('string', $section);

        $value = backport_type_check('?string', $value);

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                self::$data[$section][] = [$key, $value];
            }
        } elseif (is_callable($data) || ($value === null && class_exists($data))) {
            self::$data[$section][] = $data;
        } else {
            self::$data[$section][] = [$data, $value];
        }
    }

    /**
     * Get the sections provided to the command.
     *
     * @return array
     */
    protected function sections()
    {
        $onlyOption = $this->option('only');

        return array_filter(explode(',', isset($onlyOption) ? $onlyOption : ''));
    }
}
