<?php

/*declare(strict_types=1);*/

namespace NunoMaduro\Collision\Adapters\Phpunit\Printers;

use Throwable;

/**
 * @internal
 *
 * @mixin DefaultPrinter
 */
final class ReportablePrinter
{
    private /*readonly DefaultPrinter */$printer;

    /**
     * Creates a new Printer instance.
     */
    public function __construct(/*private readonly */DefaultPrinter $printer)
    {
        $this->printer = $printer;

        // ..
    }

    /**
     * Calls the original method, but reports any errors to the reporter.
     */
    public function __call(/*string */$name, array $arguments)/*: mixed*/
    {
        $name = backport_type_check('string', $name);

        try {
            return $this->printer->$name(...$arguments);
        } catch (\Exception $throwable) {
        } catch (\Error $throwable) {
        } catch (Throwable $throwable) {
        }

        if (isset($throwable)) {
            $this->printer->report($throwable);
        }

        exit(1);
    }
}
