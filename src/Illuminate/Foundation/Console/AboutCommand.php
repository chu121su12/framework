<?php

namespace Illuminate\Foundation\Console;

use Closure;
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
                return $this->option('only') ? in_array($this->toSearchKeyword($key), $this->sections()) : true;
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

                    $this->components->twoColumnDetail($label, value($value, false));
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
            return [
                (string) Str::of($section)->snake() => $data->mapWithKeys(function ($item, $key) {
                    return [
                        $this->toSearchKeyword($item[0]) => value($item[1], true),
                    ];
                }),
            ];
        });

        $this->output->writeln(strip_tags(backport_json_encode($output)));
    }

    /**
     * Gather information about the application.
     *
     * @return void
     */
    protected function gatherApplicationInformation()
    {
        $formatEnabledStatus = function ($value) { return $value ? '<fg=yellow;options=bold>ENABLED</>' : 'OFF'; };
        $formatCachedStatus = function ($value) { return $value ? '<fg=green;options=bold>CACHED</>' : '<fg=yellow;options=bold>NOT CACHED</>'; };

        static::addToSection('Environment', function () use ($formatEnabledStatus) { return [
            'Application Name' => config('app.name'),
            'Laravel Version' => $this->laravel->version(),
            'PHP Version' => phpversion(),
            'Composer Version' => $this->composer->getVersion() !== null ? $this->composer->getVersion() : '<fg=yellow;options=bold>-</>',
            'Environment' => $this->laravel->environment(),
            'Debug Mode' => static::format(config('app.debug'), /*console: */$formatEnabledStatus),
            'URL' => Str::of(config('app.url'))->replace(['http://', 'https://'], ''),
            'Maintenance Mode' => static::format($this->laravel->isDownForMaintenance(), /*console: */$formatEnabledStatus),
        ]; });

        static::addToSection('Cache', function () use ($formatCachedStatus) { return [
            'Config' => static::format($this->laravel->configurationIsCached(), /*console: */$formatCachedStatus),
            'Events' => static::format($this->laravel->eventsAreCached(), /*console: */$formatCachedStatus),
            'Routes' => static::format($this->laravel->routesAreCached(), /*console: */$formatCachedStatus),
            'Views' => static::format($this->hasPhpFiles($this->laravel->storagePath('framework/views')), /*console: */$formatCachedStatus),
        ]; });

        static::addToSection('Drivers', function () { return array_filter([
            'Broadcasting' => config('broadcasting.default'),
            'Cache' => config('cache.default'),
            'Database' => config('database.default'),
            'Logs' => function ($json) {
                $logChannel = config('logging.default');

                if (config('logging.channels.'.$logChannel.'.driver') === 'stack') {
                    $secondary = collect(config('logging.channels.'.$logChannel.'.channels'));

                    return value(static::format(
                        /*value: */$logChannel,
                        /*console: */function ($value) use ($secondary) { return '<fg=yellow;options=bold>'.$value.'</> <fg=gray;options=bold>/</> '.$secondary->implode(', '); },
                        /*json: */function () use ($secondary) { return $secondary->all(); }
                    ), $json);
                } else {
                    $logs = $logChannel;
                }

                return $logs;
            },
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

        $value = backport_type_check('?string', $value);

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
        $only = $this->option('only');

        return collect(explode(',', isset($only) ? $only : ''))
            ->filter()
            ->map(function ($only) { return $this->toSearchKeyword($only); })
            ->all();
    }

    /**
     * Materialize a function that formats a given value for CLI or JSON output.
     *
     * @param  mixed  $value
     * @param  (\Closure(mixed):(mixed))|null  $console
     * @param  (\Closure(mixed):(mixed))|null  $json
     * @return \Closure(bool):mixed
     */
    public static function format($value, Closure $console = null, Closure $json = null)
    {
        return function ($isJson) use ($value, $console, $json) {
            if ($isJson === true && $json instanceof Closure) {
                return value($json, $value);
            } elseif ($isJson === false && $console instanceof Closure) {
                return value($console, $value);
            }

            return value($value);
        };
    }

    /**
     * Format the given string for searching.
     *
     * @param  string  $value
     * @return string
     */
    protected function toSearchKeyword(/*string */$value)
    {
        $value = backport_type_check('string', $value);

        return (string) Str::of($value)->lower()->snake();
    }
}
