<?php

namespace Illuminate\Foundation\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'optimize:clear')]
class OptimizeClearCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'optimize:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove the cached bootstrap files';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->components->info('Clearing cached bootstrap files.');

        collect([
            'events' => function () { return $this->callSilent('event:clear') == 0; },
            'views' => function () { return $this->callSilent('view:clear') == 0; },
            'cache' => function () { return $this->callSilent('cache:clear') == 0; },
            'route' => function () { return $this->callSilent('route:clear') == 0; },
            'config' => function () { return $this->callSilent('config:clear') == 0; },
            'compiled' => function () { return $this->callSilent('clear-compiled') == 0; },
        ])->each(function ($task, $description) { return $this->components->task($description, $task); });

        $this->newLine();
    }
}
