<?php

namespace Illuminate\Console\View\Components;

use CR\LaravelBackport\SymfonyHelper;
use Symfony\Component\Console\Output\OutputInterface;
use function Termwind\terminal;
use Throwable;

class Task extends Component
{
    /**
     * Renders the component using the given arguments.
     *
     * @param  string  $description
     * @param  (callable(): bool)|null  $task
     * @param  int  $verbosity
     * @return void
     */
    public function render($description, $task = null, $verbosity = OutputInterface::VERBOSITY_NORMAL)
    {
        $description = $this->mutate($description, [
            Mutators\EnsureDynamicContentIsHighlighted::class,
            Mutators\EnsureNoPunctuation::class,
            Mutators\EnsureRelativePaths::class,
        ]);

        $cleanDescription = preg_replace("/\<[\w=#\/\;,:.&,%?]+\>|\\e\[\d+m/", '$1', $description);
        $descriptionWidth = mb_strlen(isset($cleanDescription) ? $cleanDescription : '');

        $this->output->write("  $description ", false, $verbosity);

        $startTime = microtime(true);

        $result = false;

        try {
            $callable = $task ?: function () { return true; };
            $result = $callable();
        // } catch (Throwable $e) {
        //     throw $e;
        } finally {
            $runTime = $task
                ? (' '.number_format((microtime(true) - $startTime) * 1000, 2).'ms')
                : '';


            $runTimeWidth = mb_strlen($runTime);
            $width = min(terminal()->width(), 150);
            $dots = max($width - $descriptionWidth - $runTimeWidth - 10, 0);

            // black < gray
            $this->output->write(str_repeat('<fg=black>.</>', $dots), false, $verbosity);
            $this->output->write("<fg=black>$runTime</>", false, $verbosity);

            $this->output->writeln(
                $result !== false ? ' <fg=green;options=bold>DONE</>' : ' <fg=red;options=bold>FAIL</>',
                $verbosity
            );
        }
    }
}
