<?php

namespace Illuminate\Foundation\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'optimize')]
class OptimizeCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'optimize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cache framework bootstrap, configuration, and metadata to increase performance';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->components->info('Caching framework bootstrap, configuration, and metadata.');

        collect([
            'config' => function () { return $this->callSilent('config:cache') == 0; },
            'events' => function () { return $this->callSilent('event:cache') == 0; },
            'routes' => function () { return $this->callSilent('route:cache') == 0; },
            'views' => function () { return $this->callSilent('view:cache') == 0; },
        ])->each(function ($task, $description) { return $this->components->task($description, $task); });

        $this->newLine();
    }
}
