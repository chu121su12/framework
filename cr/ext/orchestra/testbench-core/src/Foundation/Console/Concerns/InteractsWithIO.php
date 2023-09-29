<?php

namespace Orchestra\Testbench\Foundation\Console\Concerns;

/**
 * @deprecated
 */
trait InteractsWithIO
{
    /**
     * Write a status message to the console.
     *
     * @param  string  $from
     * @param  string  $to
     * @param  string  $type
     * @param  string|null  $workingPath
     * @return void
     */
    protected function copyTaskCompleted(/*string */$from, /*string */$to, /*string */$type, /*?string */$workingPath = null)/*: void*/
    {
        $from = backport_type_check('string', $from);
        $to = backport_type_check('string', $to);
        $type = backport_type_check('string', $type);
        $workingPath = backport_type_check('?string', $workingPath);

        /** @phpstan-ignore-next-line */
        $workingPath = isset($workingPath) ? $workingPath : TESTBENCH_WORKING_PATH;

        $from = str_replace($workingPath.'/', '', (string) realpath($from));

        $to = str_replace($workingPath.'/', '', (string) realpath($to));

        $this->components->task(sprintf(
            'Copying %s [%s] to [%s]',
            $type,
            $from,
            $to
        ));
    }
}
