<?php

namespace Illuminate\Foundation\Concerns;

use Throwable;

trait ResolvesDumpSource
{
    /**
     * All of the href formats for common editors.
     *
     * @var array<string, string>
     */
    protected $editorHrefs = [
        'atom' => 'atom://core/open/file?filename={file}&line={line}',
        'emacs' => 'emacs://open?url=file://{file}&line={line}',
        'idea' => 'idea://open?file={file}&line={line}',
        'macvim' => 'mvim://open/?url=file://{file}&line={line}',
        'netbeans' => 'netbeans://open/?f={file}:{line}',
        'nova' => 'nova://core/open/file?filename={file}&line={line}',
        'phpstorm' => 'phpstorm://open?file={file}&line={line}',
        'sublime' => 'subl://open?url=file://{file}&line={line}',
        'textmate' => 'txmt://open?url=file://{file}&line={line}',
        'vscode' => 'vscode://file/{file}:{line}',
        'vscode-insiders' => 'vscode-insiders://file/{file}:{line}',
        'vscode-insiders-remote' => 'vscode-insiders://vscode-remote/{file}:{line}',
        'vscode-remote' => 'vscode://vscode-remote/{file}:{line}',
        'vscodium' => 'vscodium://file/{file}:{line}',
        'xdebug' => 'xdebug://{file}@{line}',
    ];

    /**
     * Files that require special trace handling and their levels.
     *
     * @var array<string, int>
     */
    protected static $adjustableTraces = [
        'symfony/var-dumper/Resources/functions/dump.php' => 1,
        'Illuminate/Collections/Traits/EnumeratesValues.php' => 4,
    ];

    /**
     * The source resolver.
     *
     * @var (callable(): (array{0: string, 1: string, 2: int|null}|null))|null|false
     */
    protected static $dumpSourceResolver;

    /**
     * Resolve the source of the dump call.
     *
     * @return array{0: string, 1: string, 2: int|null}|null
     */
    public function resolveDumpSource()
    {
        if (static::$dumpSourceResolver === false) {
            return null;
        }

        if (static::$dumpSourceResolver) {
            return call_user_func(static::$dumpSourceResolver);
        }

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 20);

        $sourceKey = null;

        foreach ($trace as $traceKey => $traceFile) {
            if (! isset($traceFile['file'])) {
                continue;
            }

            foreach (self::$adjustableTraces as $name => $key) {
                if (str_ends_with(
                    $traceFile['file'],
                    str_replace('/', DIRECTORY_SEPARATOR, $name)
                )) {
                    $sourceKey = $traceKey + $key;
                    break;
                }
            }

            if (! is_null($sourceKey)) {
                break;
            }
        }

        if (is_null($sourceKey)) {
            return;
        }

        $file = isset($trace[$sourceKey]) && isset($trace[$sourceKey]['file']) ? $trace[$sourceKey]['file'] : null;
        $line = isset($trace[$sourceKey]) && isset($trace[$sourceKey]['line']) ? $trace[$sourceKey]['line'] : null;

        if (is_null($file) || is_null($line)) {
            return;
        }

        $relativeFile = $file;

        if ($this->isCompiledViewFile($file)) {
            $file = $this->getOriginalFileForCompiledView($file);
            $line = null;
        }

        if (str_starts_with($file, $this->basePath)) {
            $relativeFile = substr($file, strlen($this->basePath) + 1);
        }

        return [$file, $relativeFile, $line];
    }

    /**
     * Determine if the given file is a view compiled.
     *
     * @param  string  $file
     * @return bool
     */
    protected function isCompiledViewFile($file)
    {
        return str_starts_with($file, $this->compiledViewPath);
    }

    /**
     * Get the original view compiled file by the given compiled file.
     *
     * @param  string  $file
     * @return string
     */
    protected function getOriginalFileForCompiledView($file)
    {
        preg_match('/\/\*\*PATH\s(.*)\sENDPATH/', file_get_contents($file), $matches);

        if (isset($matches[1])) {
            $file = $matches[1];
        }

        return $file;
    }

    /**
     * Resolve the source href, if possible.
     *
     * @param  string  $file
     * @param  int|null  $line
     * @return string|null
     */
    protected function resolveSourceHref($file, $line)
    {
        try {
            $editor = config('app.editor');
        } catch (\Exception $e) {
        } catch (\ErrorException $e) {
        } catch (Throwable $e) {
            // ..
        }

        if (! isset($editor)) {
            return;
        }

        if (is_array($editor) && isset($editor['href'])) {
            $href = $editor['href'];
        } else {
            $hrefKey = isset($editor['name']) ? $editor['name'] : $editor;

            $href = isset($this->editorHrefs[$hrefKey])
                ? $this->editorHrefs[$hrefKey]
                : sprintf('%s://open?file={file}&line={line}', $hrefKey);
        }

        if ($basePath = (isset($editor['base_path']) ? $editor['base_path'] : false)) {
            $file = str_replace($this->basePath, $basePath, $file);
        }

        $href = str_replace(
            ['{file}', '{line}'],
            [$file, is_null($line) ? 1 : $line],
            $href
        );

        return $href;
    }

    /**
     * Set the resolver that resolves the source of the dump call.
     *
     * @param  (callable(): (array{0: string, 1: string, 2: int|null}|null))|null  $callable
     * @return void
     */
    public static function resolveDumpSourceUsing($callable)
    {
        static::$dumpSourceResolver = $callable;
    }

    /**
     * Don't include the location / file of the dump in dumps.
     *
     * @return void
     */
    public static function dontIncludeSource()
    {
        static::$dumpSourceResolver = false;
    }
}
