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
    protected $description = 'Cache the framework bootstrap files';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->components->info('Caching the framework bootstrap files');

        collect([
            'config' => function () { return $this->callSilent('config:cache') == 0; },
            'routes' => function () { return $this->callSilent('route:cache') == 0; },
        ])->each(function ($task, $description) { return $this->components->task($description, $task); });

        $this->newLine();
    }
}
