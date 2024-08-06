<?php

namespace Illuminate\Foundation\Console;

use CR\LaravelBackport\SymfonyHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'config:show')]
class ConfigShowCommand extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'config:show {config : The configuration file or key to show}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display all of the values for a given configuration file or key';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $config = $this->argument('config');

        if (! config()->has($config)) {
            $this->fail("Configuration file or key <comment>{$config}</comment> does not exist.");
        }

        $this->newLine();
        $this->render($config);
        $this->newLine();

        return SymfonyHelper::CONSOLE_SUCCESS;
    }

    /**
     * Render the configuration values.
     *
     * @param  string  $name
     * @return void
     */
    public function render($name)
    {
        $data = config($name);

        if (! is_array($data)) {
            $this->title($name, $this->formatValue($data));

            return;
        }

        $this->title($name);

        foreach (Arr::dot($data) as $key => $value) {
            $this->components->twoColumnDetail(
                $this->formatKey($key),
                $this->formatValue($value)
            );
        }
    }

    /**
     * Render the title.
     *
     * @param  string  $title
     * @param  string|null  $subtitle
     * @return void
     */
    public function title($title, $subtitle = null)
    {
        $this->components->twoColumnDetail(
            "<fg=green;options=bold>{$title}</>",
            $subtitle
        );
    }

    /**
     * Format the given configuration key.
     *
     * @param  string  $key
     * @return string
     */
    protected function formatKey($key)
    {
        return preg_replace_callback(
            '/(.*)\.(.*)$/', function ($matches) {
                return sprintf(
                    '<fg=gray>%s ⇁</> %s',
                    str_replace('.', ' ⇁ ', $matches[1]),
                    $matches[2]
                );
            }, $key
        );
    }

    /**
     * Format the given configuration value.
     *
     * @param  mixed  $value
     * @return string
     */
    protected function formatValue($value)
    {
        switch (true) {
            case is_bool($value): return sprintf('<fg=#ef8414;options=bold>%s</>', $value ? 'true' : 'false');
            case is_null($value): return '<fg=#ef8414;options=bold>null</>';
            case is_numeric($value): return "<fg=#ef8414;options=bold>{$value}</>";
            case is_array($value): return '[]';
            case is_object($value): return get_class($value);
            case is_string($value): return $value;
            default: return print_r($value, true);
        }
    }
}
