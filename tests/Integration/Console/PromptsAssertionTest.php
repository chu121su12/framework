<?php

namespace Illuminate\Tests\Integration\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Kernel;
use Orchestra\Testbench\TestCase;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\multisearch;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\password;
use function Laravel\Prompts\search;
use function Laravel\Prompts\select;
use function Laravel\Prompts\suggest;
use function Laravel\Prompts\text;
use function Laravel\Prompts\textarea;

class PromptsAssertionTest_testAssertionForTextPrompt_class extends Command
                    {
                        protected $signature = 'test:text';

                        public function handle()
                        {
                            $name = text('What is your name?', 'John');

                            $this->line($name);
                        }
                    }

class PromptsAssertionTest_testAssertionForTextareaPrompt_class extends Command
                    {
                        protected $signature = 'test:textarea';

                        public function handle()
                        {
                            $name = textarea('What is your name?', 'John');

                            $this->line($name);
                        }
                    }

class PromptsAssertionTest_testAssertionForSuggestPrompt_class extends Command
                    {
                        protected $signature = 'test:suggest';

                        public function handle()
                        {
                            $name = suggest('What is your name?', ['John', 'Jane']);

                            $this->line($name);
                        }
                    }

class PromptsAssertionTest_testAssertionForPasswordPrompt_class extends Command
                    {
                        protected $signature = 'test:password';

                        public function handle()
                        {
                            $name = password('What is your password?');

                            $this->line($name);
                        }
                    }

class PromptsAssertionTest_testAssertionForConfirmPrompt_class extends Command
                    {
                        protected $signature = 'test:confirm';

                        public function handle()
                        {
                            $confirmed = confirm('Is your name John?');

                            if ($confirmed) {
                                $this->line('Your name is John.');
                            } else {
                                $this->line('Your name is not John.');
                            }
                        }
                    }

class PromptsAssertionTest_testAssertionForSelectPromptWithAList_class extends Command
                    {
                        protected $signature = 'test:select';

                        public function handle()
                        {
                            $name = select(
                                /*label: */'What is your name?',
                                /*options: */['John', 'Jane']
                            );

                            $this->line("Your name is $name.");
                        }
                    }

class PromptsAssertionTest_testAssertionForSelectPromptWithAnAssociativeArray_class extends Command
                    {
                        protected $signature = 'test:select';

                        public function handle()
                        {
                            $name = select(
                                /*label: */'What is your name?',
                                /*options: */['john' => 'John', 'jane' => 'Jane']
                            );

                            $this->line("Your name is $name.");
                        }
                    }

class PromptsAssertionTest_testAlternativeAssertionForSelectPromptWithAnAssociativeArray_class extends Command
                    {
                        protected $signature = 'test:select';

                        public function handle()
                        {
                            $name = select(
                                /*label: */'What is your name?',
                                /*options: */['john' => 'John', 'jane' => 'Jane']
                            );

                            $this->line("Your name is $name.");
                        }
                    }

class PromptsAssertionTest_testAssertionForRequiredMultiselectPrompt_class extends Command
                    {
                        protected $signature = 'test:multiselect';

                        public function handle()
                        {
                            $names = multiselect(
                                /*label: */'Which names do you like?',
                                /*options: */['John', 'Jane', 'Sally', 'Jack'],
                                $default = [],
                                $scroll = 5,
                                /*required: */true
                            );

                            $this->line(sprintf('You like %s.', implode(', ', $names)));
                        }
                    }

class PromptsAssertionTest_testAssertionForOptionalMultiselectPrompt_class extends Command
                    {
                        protected $signature = 'test:multiselect';

                        public function handle()
                        {
                            $names = multiselect(
                                /*label: */'Which names do you like?',
                                /*options: */['John', 'Jane', 'Sally', 'Jack']
                            );

                            if (empty($names)) {
                                $this->line('You like nobody.');
                            } else {
                                $this->line(sprintf('You like %s.', implode(', ', $names)));
                            }
                        }
                    }

class PromptAssertionTest_testAssertionForSearchPrompt_class extends Command
                    {
                        protected $signature = 'test:search';
        
                        public function handle()
                        {
                            $options = collect(['John', 'Jane', 'Sally', 'Jack']);
        
                            $name = search(
                                /*label: */'What is your name?',
                                /*options: */function (/*string */$value) use ($options) {
                                    $value = backport_type_check('string', $value);

                                    return strlen($value) > 0
                                        ? $options->filter(function ($title) use ($value) {
                                            return str_contains($title, $value);
                                        })->values()->toArray()
                                        : [];
                                }
                            );
        
                            $this->line("Your name is $name.");
                        }
                    }
        
class PromptAssertionTest_testAssertionForMultisearchPrompt_class extends Command
                    {
                        protected $signature = 'test:multisearch';
        
                        public function handle()
                        {
                            $options = collect(['John', 'Jane', 'Sally', 'Jack']);
        
                            $names = multisearch(
                                /*label: */'Which names do you like?',
                                /*options: */function (/*string */$value) use ($options) {
                                    $value = backport_type_check('string', $value);

                                    return strlen($value) > 0
                                        ? $options->filter(function ($title) use ($value) {
                                            return str_contains($title, $value);
                                        })->values()->toArray()
                                        : [];
                                }
                            );
        
                            if (empty($names)) {
                                $this->line('You like nobody.');
                            } else {
                                $this->line(sprintf('You like %s.', implode(', ', $names)));
                            }
                        }
                    }
        
class PromptAssertionTest_testAssertionForSelectPromptFollowedByMultisearchPrompt_class extends Command
                    {
                        protected $signature = 'test:select';
        
                        public function handle()
                        {
                            $name = select(
                                /*label: */'What is your name?',
                                /*options: */['John', 'Jane']
                            );
        
                            $titles = collect(['Mr', 'Mrs', 'Ms', 'Dr']);
                            $title = multisearch(
                                /*label: */'What is your title?',
                                /*options: */function (/*string */$value) use ($titles) {
                                    $value = backport_type_check('string', $value);

                                    return strlen($value) > 0
                                        ? $titles->filter(function ($title) use ($value) {
                                            return str_contains($title, $value);
                                        })->values()->toArray()
                                        : [];
                                }
                            );
        
                            $this->line('I will refer to you '.$title[0].' '.$name.'.');
                        }
                    }

class PromptsAssertionTest extends TestCase
{
    public function testAssertionForTextPrompt()
    {
        $this->app[Kernel::class]->registerCommand(
            new PromptsAssertionTest_testAssertionForTextPrompt_class
        );

        $this
            ->artisan('test:text')
            ->expectsQuestion('What is your name?', 'Jane')
            ->expectsOutput('Jane');
    }

    public function testAssertionForTextareaPrompt()
    {
        $this->app[Kernel::class]->registerCommand(
            new PromptsAssertionTest_testAssertionForTextareaPrompt_class
        );

        $this
            ->artisan('test:textarea')
            ->expectsQuestion('What is your name?', 'Jane')
            ->expectsOutput('Jane');
    }

    public function testAssertionForSuggestPrompt()
    {
        $this->app[Kernel::class]->registerCommand(
            new PromptsAssertionTest_testAssertionForSuggestPrompt_class
        );

        $this
            ->artisan('test:suggest')
            ->expectsChoice('What is your name?', 'Joe', ['John', 'Jane'])
            ->expectsOutput('Joe');
    }

    public function testAssertionForPasswordPrompt()
    {
        $this->app[Kernel::class]->registerCommand(
            new PromptsAssertionTest_testAssertionForPasswordPrompt_class
        );

        $this
            ->artisan('test:password')
            ->expectsQuestion('What is your password?', 'secret')
            ->expectsOutput('secret');
    }

    public function testAssertionForConfirmPrompt()
    {
        $this->app[Kernel::class]->registerCommand(
            new PromptsAssertionTest_testAssertionForConfirmPrompt_class
        );

        $this
            ->artisan('test:confirm')
            ->expectsConfirmation('Is your name John?', 'no')
            ->expectsOutput('Your name is not John.');

        $this
            ->artisan('test:confirm')
            ->expectsConfirmation('Is your name John?', 'yes')
            ->expectsOutput('Your name is John.');
    }

    public function testAssertionForSelectPromptWithAList()
    {
        $this->app[Kernel::class]->registerCommand(
            new PromptsAssertionTest_testAssertionForSelectPromptWithAList_class
        );

        $this
            ->artisan('test:select')
            ->expectsChoice('What is your name?', 'Jane', ['John', 'Jane'])
            ->expectsOutput('Your name is Jane.');
    }

    public function testAssertionForSelectPromptWithAnAssociativeArray()
    {
        $this->app[Kernel::class]->registerCommand(
            new PromptsAssertionTest_testAssertionForSelectPromptWithAnAssociativeArray_class
        );

        $this
            ->artisan('test:select')
            ->expectsChoice('What is your name?', 'jane', ['john' => 'John', 'jane' => 'Jane'])
            ->expectsOutput('Your name is jane.');
    }

    public function testAlternativeAssertionForSelectPromptWithAnAssociativeArray()
    {
        $this->app[Kernel::class]->registerCommand(
            new PromptsAssertionTest_testAlternativeAssertionForSelectPromptWithAnAssociativeArray_class
        );

        $this
            ->artisan('test:select')
            ->expectsChoice('What is your name?', 'jane', ['john', 'jane', 'John', 'Jane'])
            ->expectsOutput('Your name is jane.');
    }

    public function testAssertionForRequiredMultiselectPrompt()
    {
        $this->app[Kernel::class]->registerCommand(
            new PromptsAssertionTest_testAssertionForRequiredMultiselectPrompt_class
        );

        $this
            ->artisan('test:multiselect')
            ->expectsChoice('Which names do you like?', ['John', 'Jane'], ['John', 'Jane', 'Sally', 'Jack'])
            ->expectsOutput('You like John, Jane.');
    }

    public function testAssertionForOptionalMultiselectPrompt()
    {
        $this->app[Kernel::class]->registerCommand(
            new PromptsAssertionTest_testAssertionForOptionalMultiselectPrompt_class
        );

        $this
            ->artisan('test:multiselect')
            ->expectsChoice('Which names do you like?', ['John', 'Jane'], ['John', 'Jane', 'Sally', 'Jack'])
            ->expectsOutput('You like John, Jane.');

        $this
            ->artisan('test:multiselect')
            ->expectsChoice('Which names do you like?', ['None'], ['John', 'Jane', 'Sally', 'Jack'])
            ->expectsOutput('You like nobody.');
    }

    public function testAssertionForSearchPrompt()
    {
        $this->app[Kernel::class]->registerCommand(
            new PromptAssertionTest_testAssertionForSearchPrompt_class
        );

        $this
            ->artisan('test:search')
            ->expectsSearch('What is your name?', 'Jane', 'J', ['John', 'Jane', 'Jack'])
            ->expectsOutput('Your name is Jane.');
    }

    public function testAssertionForMultisearchPrompt()
    {
        $this->app[Kernel::class]->registerCommand(
            new PromptAssertionTest_testAssertionForMultisearchPrompt_class
        );

        $this
            ->artisan('test:multisearch')
            ->expectsSearch('Which names do you like?', ['John', 'Jane'], 'J', ['John', 'Jane', 'Jack'])
            ->expectsOutput('You like John, Jane.');

        $this
            ->artisan('test:multisearch')
            ->expectsSearch('Which names do you like?', [], 'J', ['John', 'Jane', 'Jack'])
            ->expectsOutput('You like nobody.');
    }

    public function testAssertionForSelectPromptFollowedByMultisearchPrompt()
    {
        $this->app[Kernel::class]->registerCommand(
            new PromptAssertionTest_testAssertionForSelectPromptFollowedByMultisearchPrompt_class
        );

        $this
            ->artisan('test:select')
            ->expectsChoice('What is your name?', 'Jane', ['John', 'Jane'])
            ->expectsSearch('What is your title?', ['Dr'], 'D', ['Dr'])
            ->expectsOutput('I will refer to you Dr Jane.');
    }
}
