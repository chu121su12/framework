<?php

/*declare(strict_types=1);*/

namespace NunoMaduro\Collision;

use Closure;
use NunoMaduro\Collision\Contracts\RenderableOnCollisionEditor;
use NunoMaduro\Collision\Contracts\RenderlessEditor;
use NunoMaduro\Collision\Contracts\RenderlessTrace;
use NunoMaduro\Collision\Contracts\SolutionsRepository;
use NunoMaduro\Collision\Exceptions\TestException;
use NunoMaduro\Collision\SolutionsRepositories\NullSolutionsRepository;
use Symfony\Component\Console\Output\BackportOutputWrapper;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;
use Whoops\Exception\Frame;
use Whoops\Exception\Inspector;

/**
 * @internal
 *
 * @see \Tests\Unit\WriterTest
 */
final class Writer
{
    /**
     * The number of frames if no verbosity is specified.
     */
    /*public */const VERBOSITY_NORMAL_FRAMES = 1;

    /**
     * Holds an instance of the solutions repository.
     *
     * @var \NunoMaduro\Collision\Contracts\SolutionsRepository
     */
    private /*SolutionsRepository */$solutionsRepository;

    /**
     * Holds an instance of the Output.
     *
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private /*OutputInterface */$output;

    /**
     * Holds an instance of the Argument Formatter.
     *
     * @var \NunoMaduro\Collision\Contracts\ArgumentFormatter
     */
    private /*ArgumentFormatter */$argumentFormatter;

    /**
     * Holds an instance of the Highlighter.
     *
     * @var \NunoMaduro\Collision\Contracts\Highlighter
     */
    private /*Highlighter */$highlighter;

    /**
     * Ignores traces where the file string matches one
     * of the provided regex expressions.
     *
     * @var array<int, string|Closure>
     */
    private /*array */$ignore = [];

    /**
     * Declares whether or not the trace should appear.
     *
     * @var bool
     */
    private /*bool */$showTrace = true;

    /**
     * Declares whether or not the title should appear.
     *
     * @var bool
     */
    private /*bool */$showTitle = true;

    /**
     * Declares whether or not the editor should appear.
     *
     * @var bool
     */
    private /*bool */$showEditor = true;

    /**
     * Creates an instance of the writer.
     */
    public function __construct(
        SolutionsRepository $solutionsRepository = null,
        OutputInterface $output = null,
        ArgumentFormatter $argumentFormatter = null,
        Highlighter $highlighter = null
    ) {
        $this->solutionsRepository = $solutionsRepository ?: new NullSolutionsRepository();
        $this->output = BackportOutputWrapper::wrap($output ?: new ConsoleOutput());
        $this->argumentFormatter = $argumentFormatter ?: new ArgumentFormatter();
        $this->highlighter = $highlighter ?: new Highlighter();
    }

    /**
     * @inheritdoc
     */
    public function write(Inspector $inspector)/*: void*/
    {
        $this->renderTitleAndDescription($inspector);

        $frames = $this->getFrames($inspector);

        $exception = $inspector->getException();

        if ($exception instanceof RenderableOnCollisionEditor) {
            $editorFrame = $exception->toCollisionEditor();
        } else {
            $editorFrame = array_shift($frames);
        }

        if ($this->showEditor
            && $editorFrame !== null
            && ! $exception instanceof RenderlessEditor
        ) {
            $this->renderEditor($editorFrame);
        }

        $this->renderSolution($inspector);

        if ($this->showTrace && ! empty($frames) && ! $exception instanceof RenderlessTrace) {
            $this->renderTrace($frames);
        } elseif (! $exception instanceof RenderlessEditor) {
            $this->output->writeln('');
        }
    }

    /**
     * @inheritdoc
     */
    public function ignoreFilesIn(array $ignore)/*: self*/
    {
        $this->ignore = $ignore;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function showTrace(/*bool */$show)/*: self*/
    {
        $show = backport_type_check('bool', $show);

        $this->showTrace = $show;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function showTitle(/*bool */$show)/*: self*/
    {
        $show = backport_type_check('bool', $show);

        $this->showTitle = $show;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function showEditor(/*bool */$show)/*: self*/
    {
        $show = backport_type_check('bool', $show);

        $this->showEditor = $show;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setOutput(OutputInterface $output)/*: self*/
    {
        $this->output = $output;
        $this->output = BackportOutputWrapper::wrap($this->output);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getOutput()/*: OutputInterface*/
    {
        return $this->output;
    }

    /**
     * Returns pertinent frames.
     *
     * @return array<int, Frame>
     */
    private function getFrames(Inspector $inspector)/*: array*/
    {
        return $inspector->getFrames()
            ->filter(
                function ($frame) {
                    // If we are in verbose mode, we always
                    // display the full stack trace.
                    if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                        return true;
                    }

                    foreach ($this->ignore as $ignore) {
                        if (is_string($ignore)) {
                            // Ensure paths are linux-style (like the ones on $this->ignore)
                            $sanitizedPath = (string) str_replace('\\', '/', $frame->getFile());
                            if (preg_match($ignore, $sanitizedPath)) {
                                return false;
                            }
                        }

                        if ($ignore instanceof Closure) {
                            if ($ignore($frame)) {
                                return false;
                            }
                        }
                    }

                    return true;
                }
            )
            ->getArray();
    }

    /**
     * Renders the title of the exception.
     */
    private function renderTitleAndDescription(Inspector $inspector)/*: self*/
    {
        /** @var Throwable|TestException $exception */
        $exception = $inspector->getException();
        $message = rtrim($exception->getMessage());
        $class = $exception instanceof TestException
            ? $exception->getClassName()
            : $inspector->getExceptionName();

        if ($this->showTitle) {
            $this->render("<bg=red;options=bold> $class </>");
            $this->output->writeln('');
        }

        $this->output->writeln("<fg=default;options=bold>  $message</>");

        return $this;
    }

    /**
     * Renders the solution of the exception, if any.
     */
    private function renderSolution(Inspector $inspector)/*: self*/
    {
        $throwable = $inspector->getException();

        $solutions = backport_instanceof_throwable($throwable)
            ? $this->solutionsRepository->getFromThrowable($throwable)
            : []; // @phpstan-ignore-line

        foreach ($solutions as $solution) {
            /** @var \Spatie\Ignition\Contracts\Solution $solution */
            $title = $solution->getSolutionTitle();
            $description = $solution->getSolutionDescription();
            $links = $solution->getDocumentationLinks();

            $description = trim((string) preg_replace("/\n/", "\n    ", $description));

            $this->render(sprintf(
                '<fg=cyan;options=bold>i</>   <fg=default;options=bold>%s</>: %s %s',
                rtrim($title, '.'),
                $description,
                implode(', ', array_map(function (/*string */$link) {
                    $link = backport_type_check('string', $link);

                    return sprintf("\n      <fg=gray>%s</>", $link);
                }, $links))
            ));
        }

        return $this;
    }

    /**
     * Renders the editor containing the code that was the
     * origin of the exception.
     */
    private function renderEditor(Frame $frame)/*: self*/
    {
        if ($frame->getFile() !== 'Unknown') {
            $file = $this->getFileRelativePath((string) $frame->getFile());

            // getLine() might return null so cast to int to get 0 instead
            $line = (int) $frame->getLine();
            $this->render('at <fg=green>'.$file.'</>'.':<fg=green>'.$line.'</>');

            $content = $this->highlighter->highlight((string) $frame->getFileContents(), (int) $frame->getLine());

            $this->output->writeln($content);
        }

        return $this;
    }

    /**
     * Renders the trace of the exception.
     */
    private function renderTrace(array $frames)/*: self*/
    {
        $vendorFrames = 0;
        $userFrames = 0;

        if (! empty($frames)) {
            $this->output->writeln(['']);
        }

        foreach ($frames as $i => $frame) {
            if ($this->output->getVerbosity() < OutputInterface::VERBOSITY_VERBOSE && strpos($frame->getFile(), '/vendor/') !== false) {
                $vendorFrames++;

                continue;
            }

            if ($userFrames > self::VERBOSITY_NORMAL_FRAMES && $this->output->getVerbosity() < OutputInterface::VERBOSITY_VERBOSE) {
                break;
            }

            $userFrames++;

            $file = $this->getFileRelativePath($frame->getFile());
            $line = $frame->getLine();
            $class = empty($frame->getClass()) ? '' : $frame->getClass().'::';
            $function = $frame->getFunction();
            $args = $this->argumentFormatter->format($frame->getArgs());
            $pos = str_pad((string) ((int) $i + 1), 4, ' ');

            if ($vendorFrames > 0) {
                $this->output->writeln(
                    sprintf("      \e[2m+%s vendor frames \e[22m", $vendorFrames)
                );
                $vendorFrames = 0;
            }

            $this->render("<fg=yellow>$pos</><fg=default;options=bold>$file</>:<fg=default;options=bold>$line</>", (bool) $class && $i > 0);
            if ($class) {
                $this->render("<fg=gray>    $class$function($args)</>", false);
            }
        }

        if (! empty($frames)) {
            $this->output->writeln(['']);
        }

        return $this;
    }

    /**
     * Renders an message into the console.
     *
     * @return $this
     */
    private function render(/*string */$message, /*bool */$break = true)/*: self*/
    {
        $message = backport_type_check('string', $message);
        $break = backport_type_check('bool', $break);

        if ($break) {
            $this->output->writeln('');
        }

        $this->output->writeln("  $message");

        return $this;
    }

    /**
     * Returns the relative path of the given file path.
     */
    private function getFileRelativePath(/*string */$filePath)/*: string*/
    {
        $filePath = backport_type_check('string', $filePath);

        $cwd = (string) getcwd();

        if (! empty($cwd)) {
            return str_replace("$cwd".DIRECTORY_SEPARATOR, '', $filePath);
        }

        return $filePath;
    }
}
