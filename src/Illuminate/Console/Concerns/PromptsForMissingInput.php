<?php

namespace Illuminate\Console\Concerns;

use Closure;
use Illuminate\Contracts\Console\PromptsForMissingInput as PromptsForMissingInputContract;
use Illuminate\Support\Arr;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function Laravel\Prompts\text;

trait PromptsForMissingInput
{
    /**
     * Interact with the user before validating the input.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return void
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        parent::interact($input, $output);

        if ($this instanceof PromptsForMissingInputContract) {
            $this->promptForMissingArguments($input, $output);
        }
    }

    /**
     * Prompt the user for any missing arguments.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return void
     */
    protected function promptForMissingArguments(InputInterface $input, OutputInterface $output)
    {
        $prompted = collect($this->getDefinition()->getArguments())
            ->reject(function (InputArgument $argument) { return $argument->getName() === 'command'; })
            ->filter(function (InputArgument $argument) use ($input) {
                if (! $argument->isRequired()) {
                    return false;
                }

                switch (true) {
                    case $argument->isArray(): return empty($input->getArgument($argument->getName()));
                    default: return is_null($input->getArgument($argument->getName()));
                }
            })
            ->each(function (InputArgument $argument) use ($input) {
                $prompts = $this->promptForMissingArgumentsUsing();
                $argumentName = $argument->getName();

                $label = isset($prompts[$argumentName]) ? $prompts[$argumentName] :
                    'What is '.lcfirst($argument->getDescription() ?: ('the '.$argument->getName())).'?';

                if ($label instanceof Closure) {
                    return $input->setArgument($argument->getName(), $argument->isArray() ? Arr::wrap($label()) : $label());
                }

                if (is_array($label)) {
                    list($label, $placeholder) = $label;
                }

                $answer = text(
                    /*label: */$label,
                    /*placeholder: */isset($placeholder) ? $placeholder : '',
                    /*$default = */'',
                    /*$required = */false,
                    /*validate: */function ($value) use ($argument) {
                        return empty($value) ? "The {$argument->getName()} is required." : null;
                    }
                );

                $input->setArgument($argument->getName(), $argument->isArray() ? [$answer] : $answer);
            })
            ->isNotEmpty();

        if ($prompted) {
            $this->afterPromptingForMissingArguments($input, $output);
        }
    }

    /**
     * Prompt for missing input arguments using the returned questions.
     *
     * @return array
     */
    protected function promptForMissingArgumentsUsing()
    {
        return [];
    }

    /**
     * Perform actions after the user was prompted for missing arguments.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return void
     */
    protected function afterPromptingForMissingArguments(InputInterface $input, OutputInterface $output)
    {
        //
    }

    /**
     * Whether the input contains any options that differ from the default values.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @return bool
     */
    protected function didReceiveOptions(InputInterface $input)
    {
        return collect($this->getDefinition()->getOptions())
            ->reject(function ($option) use ($input) {
                return $input->getOption($option->getName()) === $option->getDefault();
            })
            ->isNotEmpty();
    }
}
