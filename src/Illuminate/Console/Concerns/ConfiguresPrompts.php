<?php

namespace Illuminate\Console\Concerns;

use Laravel\Prompts\ConfirmPrompt;
use Laravel\Prompts\MultiSearchPrompt;
use Laravel\Prompts\MultiSelectPrompt;
use Laravel\Prompts\PasswordPrompt;
use Laravel\Prompts\Prompt;
use Laravel\Prompts\SearchPrompt;
use Laravel\Prompts\SelectPrompt;
use Laravel\Prompts\SuggestPrompt;
use Laravel\Prompts\TextPrompt;
use Symfony\Component\Console\Input\InputInterface;

trait ConfiguresPrompts
{
    /**
     * Configure the prompt fallbacks.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @return void
     */
    protected function configurePrompts(InputInterface $input)
    {
        Prompt::setOutput($this->output);

        Prompt::interactive(($input->isInteractive() && defined('STDIN') && stream_isatty(STDIN)) || $this->laravel->runningUnitTests());

        Prompt::fallbackWhen(windows_os() || $this->laravel->runningUnitTests());

        TextPrompt::fallbackUsing(function (TextPrompt $prompt) { return $this->promptUntilValid(
            function () use ($prompt) {
                $result = $this->components->ask($prompt->label, $prompt->default ?: null);

                return isset($result) ? $result : '';
            },
            $prompt->required,
            $prompt->validate
        ); });

        PasswordPrompt::fallbackUsing(function (PasswordPrompt $prompt) { return $this->promptUntilValid(
            function () use ($prompt) {
                $result = $this->components->secret($prompt->label);

                return isset($result) ? $result : '';
            },
            $prompt->required,
            $prompt->validate
        ); });

        ConfirmPrompt::fallbackUsing(function (ConfirmPrompt $prompt) { return $this->promptUntilValid(
            function () use ($prompt) { return $this->components->confirm($prompt->label, $prompt->default); },
            $prompt->required,
            $prompt->validate
        ); });

        SelectPrompt::fallbackUsing(function (SelectPrompt $prompt) { return $this->promptUntilValid(
            function () use ($prompt) { return $this->components->choice($prompt->label, $prompt->options, $prompt->default); },
            false,
            $prompt->validate
        ); });

        MultiSelectPrompt::fallbackUsing(function (MultiSelectPrompt $prompt) {
            if ($prompt->default !== []) {
                return $this->promptUntilValid(
                    function () use ($prompt) { return $this->components->choice(
                        $prompt->label, $prompt->options, implode(',', $prompt->default), /*$attempts = */null, /*multiple: */true
                    ); },
                    $prompt->required,
                    $prompt->validate
                );
            }

            return $this->promptUntilValid(
                function () use ($prompt) {
                    return collect($this->components->choice(
                        $prompt->label, \array_merge(['' => 'None'], $prompt->options), 'None', /*$attempts = */null, /*multiple: */true
                    ))
                        ->reject('')
                        ->all();
                },
                $prompt->required,
                $prompt->validate
            );
        });

        SuggestPrompt::fallbackUsing(function (SuggestPrompt $prompt) { return $this->promptUntilValid(
            function () use ($prompt) {
                $result = $this->components->askWithCompletion($prompt->label, $prompt->options, $prompt->default ?: null);

                return isset($result) ? $result : '';
            },
            $prompt->required,
            $prompt->validate
        ); });

        SearchPrompt::fallbackUsing(function (SearchPrompt $prompt) { return $this->promptUntilValid(
            function () use ($prompt) {
                $query = $this->components->ask($prompt->label);

                $options = call_user_func($prompt->options, $query);

                return $this->components->choice($prompt->label, $options);
            },
            false,
            $prompt->validate
        ); });

        MultiSearchPrompt::fallbackUsing(function (MultiSearchPrompt $prompt) { return $this->promptUntilValid(
            function () use ($prompt) {
                $query = $this->components->ask($prompt->label);

                $options = call_user_func($prompt->options, $query);

                if ($prompt->required === false) {
                    if (array_is_list($options)) {
                        return collect($this->components->choice($prompt->label, \array_merge(['None'], $options), 'None', /*multiple: */true))
                            ->reject('None')
                            ->values()
                            ->all();
                    }

                    return collect($this->components->choice($prompt->label, \array_merge(['' => 'None'], $options), '', /*multiple: */true))
                        ->reject('')
                        ->values()
                        ->all();
                }

                return $this->components->choice($prompt->label, $options, /*multiple: */true);
            },
            $prompt->required,
            $prompt->validate
        ); });
    }

    /**
     * Prompt the user until the given validation callback passes.
     *
     * @param  \Closure  $prompt
     * @param  bool|string  $required
     * @param  \Closure|null  $validate
     * @return mixed
     */
    protected function promptUntilValid($prompt, $required, $validate)
    {
        while (true) {
            $result = $prompt();

            if ($required && ($result === '' || $result === [] || $result === false)) {
                $this->components->error(is_string($required) ? $required : 'Required.');

                continue;
            }

            if ($validate) {
                $error = $validate($result);

                if (is_string($error) && strlen($error) > 0) {
                    $this->components->error($error);

                    continue;
                }
            }

            return $result;
        }
    }

    /**
     * Restore the prompts output.
     *
     * @return void
     */
    protected function restorePrompts()
    {
        Prompt::setOutput($this->output);
    }
}
