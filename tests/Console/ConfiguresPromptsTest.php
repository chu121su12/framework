<?php

namespace Illuminate\Tests\Console;

use Illuminate\Console\Application;
use Illuminate\Console\Command;
use Illuminate\Console\OutputStyle;
use Illuminate\Console\View\Components\Factory;
use Laravel\Prompts\Prompt;
use Mockery as m;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\select;

class ConfiguresPromptsTest_testSelectFallback_class extends Command
        {
            public $answer;

            protected $prompt;

            public function __construct(/*protected */$prompt)
            {
                parent::__construct();

                $this->prompt = $prompt;
            }

            public function handle()
            {
                $this->answer = \call_user_func($this->prompt);
            }
        }

class ConfiguresPromptsTest_testMultiselectFallback_class extends Command
        {
            public $answer;

            protected $prompt;

            public function __construct(/*protected */$prompt)
            {
                parent::__construct();

                $this->prompt = $prompt;
            }

            public function handle()
            {
                $this->answer = \call_user_func($this->prompt);
            }
        }

class ConfiguresPromptsTest extends TestCase
{
    protected function tearDown()/*: void*/
    {
        m::close();
    }

    /** @dataProvider selectDataProvider */
    #[DataProvider('selectDataProvider')]
    public function testSelectFallback($prompt, $expectedOptions, $expectedDefault, $return, $expectedReturn)
    {
        Prompt::fallbackWhen(true);

        $command = new ConfiguresPromptsTest_testSelectFallback_class($prompt);

        $this->runCommand($command, function ($components) use ($expectedOptions, $expectedDefault, $return) { return $components
            ->expects('choice')
            ->with('Test', $expectedOptions, $expectedDefault)
            ->andReturn($return)
        ; });

        $this->assertSame($expectedReturn, $command->answer);
    }

    public static function selectDataProvider()
    {
        return [
            'list with no default' => [function () { return select('Test', ['a', 'b', 'c']); }, ['a', 'b', 'c'], null, 'b', 'b'],
            'numeric keys with no default' => [function () { return select('Test', [1 => 'a', 2 => 'b', 3 => 'c']); }, [1 => 'a', 2 => 'b', 3 => 'c'], null, '2', 2],
            'assoc with no default' => [function () { return select('Test', ['a' => 'A', 'b' => 'B', 'c' => 'C']); }, ['a' => 'A', 'b' => 'B', 'c' => 'C'], null, 'b', 'b'],
            'list with default' => [function () { return select('Test', ['a', 'b', 'c'], 'b'); }, ['a', 'b', 'c'], 'b', 'b', 'b'],
            'numeric keys with default' => [function () { return select('Test', [1 => 'a', 2 => 'b', 3 => 'c'], 2); }, [1 => 'a', 2 => 'b', 3 => 'c'], 2, '2', 2],
            'assoc with default' => [function () { return select('Test', ['a' => 'A', 'b' => 'B', 'c' => 'C'], 'b'); }, ['a' => 'A', 'b' => 'B', 'c' => 'C'], 'b', 'b', 'b'],
        ];
    }

    /** @dataProvider multiselectDataProvider */
    #[DataProvider('multiselectDataProvider')]
    public function testMultiselectFallback($prompt, $expectedOptions, $expectedDefault, $return, $expectedReturn)
    {
        Prompt::fallbackWhen(true);

        $command = new ConfiguresPromptsTest_testMultiselectFallback_class($prompt);

        $this->runCommand($command, function ($components) use ($expectedOptions, $expectedDefault, $return) { return $components
            ->expects('choice')
            ->with('Test', $expectedOptions, $expectedDefault, null, true)
            ->andReturn($return)
        ; });

        $this->assertSame($expectedReturn, $command->answer);
    }

    public static function multiselectDataProvider()
    {
        return [
            'list with no default' => [function () { return multiselect('Test', ['a', 'b', 'c']); }, ['None', 'a', 'b', 'c'], 'None', ['None'], []],
            'numeric keys with no default' => [function () { return multiselect('Test', [1 => 'a', 2 => 'b', 3 => 'c']); }, ['' => 'None', 1 => 'a', 2 => 'b', 3 => 'c'], 'None', [''], []],
            'assoc with no default' => [function () { return multiselect('Test', ['a' => 'A', 'b' => 'B', 'c' => 'C']); }, ['' => 'None', 'a' => 'A', 'b' => 'B', 'c' => 'C'], 'None', [''], []],
            'list with default' => [function () { return multiselect('Test', ['a', 'b', 'c'], ['b', 'c']); }, ['None', 'a', 'b', 'c'], 'b,c', ['b', 'c'], ['b', 'c']],
            'numeric keys with default' => [function () { return multiselect('Test', [1 => 'a', 2 => 'b', 3 => 'c'], [2, 3]); }, ['' => 'None', 1 => 'a', 2 => 'b', 3 => 'c'], '2,3', ['2', '3'], [2, 3]],
            'assoc with default' => [function () { return multiselect('Test', ['a' => 'A', 'b' => 'B', 'c' => 'C'], ['b', 'c']); }, ['' => 'None', 'a' => 'A', 'b' => 'B', 'c' => 'C'], 'b,c', ['b', 'c'], ['b', 'c']],
            'required list with no default' => [function () { return multiselect('Test', ['a', 'b', 'c'], $default = [], $scroll = 5, /*required: */true); }, ['a', 'b', 'c'], null, ['b', 'c'], ['b', 'c']],
            'required numeric keys with no default' => [function () { return multiselect('Test', [1 => 'a', 2 => 'b', 3 => 'c'], $default = [], $scroll = 5, /*required: */true); }, [1 => 'a', 2 => 'b', 3 => 'c'], null, ['2', '3'], [2, 3]],
            'required assoc with no default' => [function () { return multiselect('Test', ['a' => 'A', 'b' => 'B', 'c' => 'C'], $default = [], $scroll = 5, /*required: */true); }, ['a' => 'A', 'b' => 'B', 'c' => 'C'], null, ['b', 'c'], ['b', 'c']],
            'required list with default' => [function () { return multiselect('Test', ['a', 'b', 'c'], ['b', 'c'], $scroll = 5, /*required: */true); }, ['a', 'b', 'c'], 'b,c', ['b', 'c'], ['b', 'c']],
            'required numeric keys with default' => [function () { return multiselect('Test', [1 => 'a', 2 => 'b', 3 => 'c'], [2, 3], $scroll = 5, /*required: */true); }, [1 => 'a', 2 => 'b', 3 => 'c'], '2,3', ['2', '3'], [2, 3]],
            'required assoc with default' => [function () { return multiselect('Test', ['a' => 'A', 'b' => 'B', 'c' => 'C'], ['b', 'c'], $scroll = 5, /*required: */true); }, ['a' => 'A', 'b' => 'B', 'c' => 'C'], 'b,c', ['b', 'c'], ['b', 'c']],
        ];
    }

    protected function runCommand($command, $expectations)
    {
        $command->setLaravel($application = m::mock(Application::class));

        $application->shouldReceive('make')->withArgs(function ($abstract) { return $abstract === OutputStyle::class; })->andReturn($outputStyle = m::mock(OutputStyle::class));
        $application->shouldReceive('make')->withArgs(function ($abstract) { return $abstract === Factory::class; })->andReturn($factory = m::mock(Factory::class));
        $application->shouldReceive('runningUnitTests')->andReturn(false);
        $application->shouldReceive('call')->with([$command, 'handle'])->andReturnUsing(function ($callback) { return call_user_func($callback); });
        $outputStyle->shouldReceive('newLinesWritten')->andReturn(1);

        $expectations($factory);

        $command->run(new ArrayInput([]), new NullOutput);
    }
}
