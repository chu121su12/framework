<?php

namespace Laravel\Prompts\Concerns;

use InvalidArgumentException;
use Laravel\Prompts\ConfirmPrompt;
use Laravel\Prompts\MultiSearchPrompt;
use Laravel\Prompts\MultiSelectPrompt;
use Laravel\Prompts\Note;
use Laravel\Prompts\PasswordPrompt;
use Laravel\Prompts\Progress;
use Laravel\Prompts\SearchPrompt;
use Laravel\Prompts\SelectPrompt;
use Laravel\Prompts\Spinner;
use Laravel\Prompts\SuggestPrompt;
use Laravel\Prompts\Table;
use Laravel\Prompts\TextPrompt;
use Laravel\Prompts\Themes\Default_\ConfirmPromptRenderer;
use Laravel\Prompts\Themes\Default_\MultiSearchPromptRenderer;
use Laravel\Prompts\Themes\Default_\MultiSelectPromptRenderer;
use Laravel\Prompts\Themes\Default_\NoteRenderer;
use Laravel\Prompts\Themes\Default_\PasswordPromptRenderer;
use Laravel\Prompts\Themes\Default_\ProgressRenderer;
use Laravel\Prompts\Themes\Default_\SearchPromptRenderer;
use Laravel\Prompts\Themes\Default_\SelectPromptRenderer;
use Laravel\Prompts\Themes\Default_\SpinnerRenderer;
use Laravel\Prompts\Themes\Default_\SuggestPromptRenderer;
use Laravel\Prompts\Themes\Default_\TableRenderer;
use Laravel\Prompts\Themes\Default_\TextPromptRenderer;

trait Themes
{
    /**
     * The name of the active theme.
     */
    protected static /*string */$theme = 'default';

    /**
     * The available themes.
     *
     * @var array<string, array<class-string<\Laravel\Prompts\Prompt>, class-string<object&callable>>>
     */
    protected static /*array */$themes = [
        'default' => [
            TextPrompt::class => TextPromptRenderer::class,
            PasswordPrompt::class => PasswordPromptRenderer::class,
            SelectPrompt::class => SelectPromptRenderer::class,
            MultiSelectPrompt::class => MultiSelectPromptRenderer::class,
            ConfirmPrompt::class => ConfirmPromptRenderer::class,
            SearchPrompt::class => SearchPromptRenderer::class,
            MultiSearchPrompt::class => MultiSearchPromptRenderer::class,
            SuggestPrompt::class => SuggestPromptRenderer::class,
            Spinner::class => SpinnerRenderer::class,
            Note::class => NoteRenderer::class,
            Table::class => TableRenderer::class,
            Progress::class => ProgressRenderer::class,
        ],
    ];

    /**
     * Get or set the active theme.
     *
     * @throws \InvalidArgumentException
     */
    public static function theme(/*?string */$name = null)/*: string*/
    {
        $name = backport_type_check('?string', $name);

        if ($name === null) {
            return static::$theme;
        }

        if (! isset(static::$themes[$name])) {
            throw new InvalidArgumentException("Prompt theme [{$name}] not found.");
        }

        return static::$theme = $name;
    }

    /**
     * Add a new theme.
     *
     * @param  array<class-string<\Laravel\Prompts\Prompt>, class-string<object&callable>>  $renderers
     */
    public static function addTheme(/*string */$name, array $renderers)/*: void*/
    {
        $name = backport_type_check('string', $name);

        if ($name === 'default') {
            throw new InvalidArgumentException('The default theme cannot be overridden.');
        }

        static::$themes[$name] = $renderers;
    }

    /**
     * Get the renderer for the current prompt.
     */
    protected function getRenderer()/*: callable*/
    {
        $staticClass = get_class($this);

        $className = isset(static::$themes[static::$theme]) && isset(static::$themes[static::$theme][$staticClass])
            ? static::$themes[static::$theme][$staticClass]
            : static::$themes['default'][$staticClass];

        return new $className($this);
    }

    /**
     * Render the prompt using the active theme.
     */
    protected function renderTheme()/*: string*/
    {
        $renderer = $this->getRenderer();

        return $renderer($this);
    }
}
