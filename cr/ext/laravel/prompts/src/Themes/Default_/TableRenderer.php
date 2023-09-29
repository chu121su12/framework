<?php

namespace Laravel\Prompts\Themes\Default_;

use Laravel\Prompts\Output\BufferedConsoleOutput;
use Laravel\Prompts\Table;
use Symfony\Component\Console\Helper\Table as SymfonyTable;
use Symfony\Component\Console\Helper\TableStyle;

class TableRenderer extends Renderer
{
    /**
     * Render the table.
     */
    public function __invoke(Table $table)/*: string*/
    {
        $tableStyle = (new TableStyle())
            ->setHorizontalBorderChar('─')
            ->setVerticalBorderChar('│', '│')
            ->setCellHeaderFormat($this->dim('<fg=default>%s</>'))
            ->setCellRowFormat('<fg=default>%s</>');

        if (empty($table->headers)) {
            $tableStyle->setCrossingChar('┼', '', '', '', '┤', '┘</>', '┴', '└', '├', '<fg=gray>┌', '┬', '┐');
        } else {
            $tableStyle->setCrossingChar('┼', '<fg=gray>┌', '┬', '┐', '┤', '┘</>', '┴', '└', '├');
        }

        $buffered = new BufferedConsoleOutput();

        (new SymfonyTable($buffered))
            ->setHeaders($table->headers)
            ->setRows($table->rows)
            ->setStyle($tableStyle)
            ->render();

        collect(explode(PHP_EOL, trim($buffered->content(), PHP_EOL)))
            ->each(function ($line) { return $this->line(' '.$line); });

        return $this;
    }
}
