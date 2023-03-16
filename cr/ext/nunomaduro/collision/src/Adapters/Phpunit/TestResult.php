<?php

/*declare(strict_types=1);*/

namespace NunoMaduro\Collision\Adapters\Phpunit;

use NunoMaduro\Collision\Contracts\Adapters\Phpunit\HasPrintableTestCaseName;
use NunoMaduro\Collision\Exceptions\ShouldNotHappen;
use PHPUnit\Event\Code\Test;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Code\Throwable;
use PHPUnit\Event\Test\BeforeFirstTestMethodErrored;

/**
 * @internal
 */
final class TestResult
{
    /*public */const FAIL = 'failed';

    /*public */const SKIPPED = 'skipped';

    /*public */const INCOMPLETE = 'incomplete';

    /*public */const TODO = 'todo';

    /*public */const RISKY = 'risky';

    /*public */const DEPRECATED = 'deprecated';

    /*public */const NOTICE = 'notice';

    /*public */const WARN = 'warnings';

    /*public */const RUNS = 'pending';

    /*public */const PASS = 'passed';

    /**
     * @readonly
     *
     * @var string
     */
    public /*string */$id;

    /**
     * @readonly
     *
     * @var string
     */
    public /*string */$testCaseName;

    /**
     * @readonly
     *
     * @var string
     */
    public /*string */$description;

    /**
     * @readonly
     *
     * @var string
     */
    public /*string */$type;

    /**
     * @readonly
     *
     * @var string
     */
    public /*string */$compactIcon;

    /**
     * @readonly
     *
     * @var string
     */
    public /*string */$icon;

    /**
     * @readonly
     *
     * @var string
     */
    public /*string */$compactColor;

    /**
     * @readonly
     *
     * @var string
     */
    public /*string */$color;

    /**
     * @readonly
     *
     * @var float
     */
    public /*float */$duration;

    /**
     * @readonly
     *
     * @var Throwable|null
     */
    public /*?Throwable */$throwable;

    /**
     * @readonly
     *
     * @var string
     */
    public /*string */$warning = '';

    /**
     * Creates a new TestResult instance.
     */
    private function __construct(/*string */$id, /*string */$testCaseName, /*string */$description, /*string */$type, /*string */$icon, /*string */$compactIcon, /*string */$color, /*string */$compactColor, /*Throwable */$throwable = null)
    {
        $id = backport_type_check('string', $id);
        $testCaseName = backport_type_check('string', $testCaseName);
        $description = backport_type_check('string', $description);
        $type = backport_type_check('string', $type);
        $compactIcon = backport_type_check('string', $compactIcon);
        $color = backport_type_check('string', $color);
        $compactColor = backport_type_check('string', $compactColor);
        backport_type_throwable($throwable, null);

        $this->id = $id;
        $this->testCaseName = $testCaseName;
        $this->description = $description;
        $this->type = $type;
        $this->icon = $icon;
        $this->compactIcon = $compactIcon;
        $this->color = $color;
        $this->compactColor = $compactColor;
        $this->throwable = $throwable;

        $this->duration = 0.0;

        $asWarning = $this->type === TestResult::WARN
             || $this->type === TestResult::RISKY
             || $this->type === TestResult::SKIPPED
             || $this->type === TestResult::DEPRECATED
             || $this->type === TestResult::NOTICE
             || $this->type === TestResult::INCOMPLETE;

        if ($throwable instanceof Throwable && $asWarning) {
            $this->warning = trim((string) preg_replace("/\r|\n/", ' ', $throwable->message()));
        }
    }

    /**
     * Sets the telemetry information.
     */
    public function setDuration(/*float */$duration)/*: void*/
    {
        $duration = backport_type_check('float', $duration);

        $this->duration = $duration;
    }

    /**
     * Creates a new test from the given test case.
     */
    public static function fromTestCase(Test $test, /*string */$type, /*Throwable */$throwable = null)/*: self*/
    {
        $type = backport_type_check('string', $type);
        backport_type_throwable($throwable, null);

        if (! $test instanceof TestMethod) {
            throw new ShouldNotHappen();
        }

        $testCaseClassName = $test->className();
        if (is_subclass_of($testCaseClassName, HasPrintableTestCaseName::class)) {
            $testCaseName = $testCaseClassName::getPrintableTestCaseName();
        } else {
            $testCaseName = $testCaseClassName;
        }

        $description = self::makeDescription($test);

        $icon = self::makeIcon($type);

        $compactIcon = self::makeCompactIcon($type);

        $color = self::makeColor($type);

        $compactColor = self::makeCompactColor($type);

        return new self($test->id(), $testCaseName, $description, $type, $icon, $compactIcon, $color, $compactColor, $throwable);
    }

    /**
     * Creates a new test from the given test case.
     */
    public static function fromBeforeFirstTestMethodErrored(BeforeFirstTestMethodErrored $event)/*: self*/
    {
        $eventTestClassName = $event->testClassName();
        if (is_subclass_of($eventTestClassName, HasPrintableTestCaseName::class)) {
            $testCaseName = $eventTestClassName::getPrintableTestCaseName();
        } else {
            $testCaseName = $eventTestClassName;
        }

        $description = '';

        $icon = self::makeIcon(self::FAIL);

        $compactIcon = self::makeCompactIcon(self::FAIL);

        $color = self::makeColor(self::FAIL);

        $compactColor = self::makeCompactColor(self::FAIL);

        return new self($testCaseName, $testCaseName, $description, self::FAIL, $icon, $compactIcon, $color, $compactColor, $event->throwable());
    }

    /**
     * Get the test case description.
     */
    public static function makeDescription(TestMethod $test)/*: string*/
    {
        $testCaseClassName = $test->className();
        if (is_subclass_of($testCaseClassName, HasPrintableTestCaseName::class)) {
            return $testCaseClassName::getLatestPrintableTestCaseMethodName();
        }

        $name = $test->name();

        // First, lets replace underscore by spaces.
        $name = str_replace('_', ' ', $name);

        // Then, replace upper cases by spaces.
        $name = (string) preg_replace('/([A-Z])/', ' $1', $name);

        // Finally, if it starts with `test`, we remove it.
        $name = (string) preg_replace('/^test/', '', $name);

        // Removes spaces
        $name = trim($name);

        // Lower case everything
        $name = mb_strtolower($name);

        return $name;
    }

    /**
     * Get the test case icon.
     */
    public static function makeIcon(/*string */$type)/*: string*/
    {
        $type = backport_type_check('string', $type);

        switch ($type) {
            case self::FAIL:
                return '⨯';
            case self::SKIPPED:
                return '-';
            case self::DEPRECATED:
            case self::WARN:
            case self::RISKY:
            case self::NOTICE:
                return '!';
            case self::INCOMPLETE:
                return '…';
            case self::TODO:
                return '↓';
            case self::RUNS:
                return '•';
            default:
                return '✓';
        }
    }

    /**
     * Get the test case compact icon.
     */
    public static function makeCompactIcon(/*string */$type)/*: string*/
    {
        $type = backport_type_check('string', $type);

        switch ($type) {
            case self::FAIL:
                return '⨯';
            case self::SKIPPED:
                return 's';
            case self::DEPRECATED:
            case self::NOTICE:
            case self::WARN:
            case self::RISKY:
                return '!';
            case self::INCOMPLETE:
                return 'i';
            case self::TODO:
                return 't';
            case self::RUNS:
                return '•';
            default:
                return '.';
        }
    }

    /**
     * Get the test case compact color.
     */
    public static function makeCompactColor(/*string */$type)/*: string*/
    {
        $type = backport_type_check('string', $type);

        switch ($type) {
            case self::FAIL:
                return 'red';
            case self::DEPRECATED:
            case self::NOTICE:
            case self::SKIPPED:
            case self::INCOMPLETE:
            case self::RISKY:
            case self::WARN:
            case self::RUNS:
                return 'yellow';
            case self::TODO:
                return 'cyan';
            default:
                return 'gray';
        }
    }

    /**
     * Get the test case color.
     */
    public static function makeColor(/*string */$type)/*: string*/
    {
        $type = backport_type_check('string', $type);

        switch ($type) {
            case self::TODO:
                return 'cyan';
            case self::FAIL:
                return 'red';
            case self::DEPRECATED:
            case self::NOTICE:
            case self::SKIPPED:
            case self::INCOMPLETE:
            case self::RISKY:
            case self::WARN:
            case self::RUNS:
                return 'yellow';
            default:
                return 'green';
        }
    }
}
