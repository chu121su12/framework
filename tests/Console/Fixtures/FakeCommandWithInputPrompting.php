<?php

namespace Illuminate\Tests\Console\Fixtures;

use BadMethodCallException;
use CR\LaravelBackport\SymfonyHelper;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;

class FakeCommandWithInputPrompting extends Command implements PromptsForMissingInput
{
    use \Illuminate\Console\Concerns\PromptsForMissingInput {
        askPersistently as traitAskPersistently;
    }

    protected $signature = 'fake-command-for-testing {name : An argument}';

    private $expectToRequestInput;

    public function __construct(/*private */$expectToRequestInput = true)
    {
        $this->expectToRequestInput = $expectToRequestInput;

        parent::__construct();
    }

    public function handle()/*: int*/
    {
        return SymfonyHelper::CONSOLE_SUCCESS;
    }

    private function askPersistently($question)
    {
        if (! $this->expectToRequestInput) {
            throw new BadMethodCallException('No prompts for input were expected, but a question was asked.');
        }

        return $this->traitAskPersistently($question);
    }
}
