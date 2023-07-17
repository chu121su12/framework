<?php

namespace Illuminate\Console;

use Closure;
use Illuminate\Console\Events\ArtisanStarting;
use Illuminate\Contracts\Console\Application as ApplicationContract;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ProcessUtils;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Process\PhpExecutableFinder;

class Application extends SymfonyApplication implements ApplicationContract
{
    /**
     * The Laravel application instance.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $laravel;

    /**
     * The event dispatcher instance.
     *
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected $events;

    /**
     * The output from the previous command.
     *
     * @var \Symfony\Component\Console\Output\BufferedOutput
     */
    protected $lastOutput;

    /**
     * The console application bootstrappers.
     *
     * @var array
     */
    protected static $bootstrappers = [];

    /**
     * A map of command names to classes.
     *
     * @var array
     */
    protected $commandMap = [];

    /**
     * Create a new Artisan console application.
     *
     * @param  \Illuminate\Contracts\Container\Container  $laravel
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @param  string  $version
     * @return void
     */
    public function __construct(Container $laravel, Dispatcher $events, $version)
    {
        parent::__construct('Laravel Framework', $version);

        $this->laravel = $laravel;
        $this->events = $events;
        $this->setAutoExit(false);
        $this->setCatchExceptions(false);

        $this->events->dispatch(new ArtisanStarting($this));

        $this->bootstrap();
    }

    /**
     * Determine the proper PHP executable.
     *
     * @return string
     */
    public static function phpBinary()
    {
        return ProcessUtils::escapeArgument((new PhpExecutableFinder)->find(false));
    }

    /**
     * Determine the proper Artisan executable.
     *
     * @return string
     */
    public static function artisanBinary()
    {
        return ProcessUtils::escapeArgument(defined('ARTISAN_BINARY') ? ARTISAN_BINARY : 'artisan');
    }

    /**
     * Format the given command as a fully-qualified executable command.
     *
     * @param  string  $string
     * @return string
     */
    public static function formatCommandString($string)
    {
        return sprintf('%s %s %s', static::phpBinary(), static::artisanBinary(), $string);
    }

    /**
     * Register a console "starting" bootstrapper.
     *
     * @param  \Closure  $callback
     * @return void
     */
    public static function starting(Closure $callback)
    {
        static::$bootstrappers[] = $callback;
    }

    /**
     * Bootstrap the console application.
     *
     * @return void
     */
    protected function bootstrap()
    {
        foreach (static::$bootstrappers as $bootstrapper) {
            $bootstrapper($this);
        }
    }

    /**
     * Clear the console application bootstrappers.
     *
     * @return void
     */
    public static function forgetBootstrappers()
    {
        static::$bootstrappers = [];
    }

    /**
     * Run an Artisan console command by name.
     *
     * @param  string  $command
     * @param  array  $parameters
     * @param  \Symfony\Component\Console\Output\OutputInterface|null  $outputBuffer
     * @return int
     *
     * @throws \Symfony\Component\Console\Exception\CommandNotFoundException
     */
    public function call($command, array $parameters = [], $outputBuffer = null)
    {
        list($command, $input) = $this->parseCommand($command, $parameters);

        if (! $this->has($command)) {
            throw new CommandNotFoundException(sprintf('The command "%s" does not exist.', $command));
        }

        return $this->run(
            $input, $this->lastOutput = $outputBuffer ?: new BufferedOutput
        );
    }

    /**
     * Parse the incoming Artisan command and its input.
     *
     * @param  string  $command
     * @param  array  $parameters
     * @return array
     */
    protected function parseCommand($command, $parameters)
    {
        if (is_subclass_of($command, SymfonyCommand::class)) {
            $callingClass = true;

            $command = $this->laravel->make($command)->getName();
        }

        if (! isset($callingClass) && empty($parameters)) {
            $command = $this->getCommandName($input = new StringInput($command));
        } else {
            array_unshift($parameters, $command);

            $input = new ArrayInput($parameters);
        }

        return [$command, $input];
    }

    /**
     * Get the output for the last run command.
     *
     * @return string
     */
    public function output()
    {
        return $this->lastOutput && method_exists($this->lastOutput, 'fetch')
                        ? $this->lastOutput->fetch()
                        : '';
    }

    /**
     * Add a command to the console.
     *
     * @param  \Symfony\Component\Console\Command\Command  $command
     * @return \Symfony\Component\Console\Command\Command
     */
    public function add(SymfonyCommand $command)
    {
        if ($command instanceof Command) {
            $command->setLaravel($this->laravel);
        }

        return $this->addToParent($command);
    }

    /**
     * Add the command to the parent instance.
     *
     * @param  \Symfony\Component\Console\Command\Command  $command
     * @return \Symfony\Component\Console\Command\Command
     */
    protected function addToParent(SymfonyCommand $command)
    {
        return parent::add($command);
    }

    /**
     * Add a command, resolving through the application.
     *
     * @param  \Illuminate\Console\Command|string  $command
     * @return \Symfony\Component\Console\Command\Command|null
     */
    public function resolve($command)
    {
        if (is_subclass_of($command, SymfonyCommand::class) && ($commandName = $command::getDefaultName())) {
            $this->commandMap[$commandName] = $command;

            return null;
        }

        if ($command instanceof Command) {
            return $this->add($command);
        }

        return $this->add($this->laravel->make($command));
    }

    /**
     * Resolve an array of commands through the application.
     *
     * @param  array|mixed  $commands
     * @return $this
     */
    public function resolveCommands($commands)
    {
        $commands = is_array($commands) ? $commands : func_get_args();

        foreach ($commands as $command) {
            $this->resolve($command);
        }

        return $this;
    }

    /**
     * Set the container command loader for lazy resolution.
     *
     * @return $this
     */
    public function setContainerCommandLoader()
    {
        $this->setCommandLoader(new ContainerCommandLoader($this->laravel, $this->commandMap));

        return $this;
    }

    /**
     * Get the default input definition for the application.
     *
     * This is used to add the --env option to every available command.
     *
     * @return \Symfony\Component\Console\Input\InputDefinition
     */
    protected function getDefaultInputDefinition()/*: InputDefinition*/
    {
        return tap(parent::getDefaultInputDefinition(), function ($definition) {
            $definition->addOption($this->getEnvironmentOption());
        });
    }

    /**
     * Get the global environment option for the definition.
     *
     * @return \Symfony\Component\Console\Input\InputOption
     */
    protected function getEnvironmentOption()
    {
        $message = 'The environment the command should run under';

        return new InputOption('--env', null, InputOption::VALUE_OPTIONAL, $message);
    }

    /**
     * Get the Laravel application instance.
     *
     * @return \Illuminate\Contracts\Foundation\Application
     */
    public function getLaravel()
    {
        return $this->laravel;
    }

    // override
    protected function doRunCommand_($command, $input, $output)
    {
        // $this->trySignals($command, $input, $output);

        return parent::doRunCommand($command, $input, $output);
    }

    protected function trySignals($command, $input, $output)
    {
        $commandSignals = $command instanceof SignalableCommandInterface ? $command->getSubscribedSignals() : [];
        if ($commandSignals || $this->dispatcher && $this->signalsToDispatchEvent) {
            if (!$this->signalRegistry) {
                throw new RuntimeException('Unable to subscribe to signal events. Make sure that the "pcntl" extension is installed and that "pcntl_*" functions are not disabled by your php.ini\'s "disable_functions" directive.');
            }

            if (Terminal::hasSttyAvailable()) {
                $sttyMode = shell_exec('stty -g');

                foreach ([\SIGINT, \SIGTERM] as $signal) {
                    $this->signalRegistry->register($signal, static function () use ($sttyMode) { return shell_exec('stty '.$sttyMode); });
                }
            }

            if ($this->dispatcher) {
                // We register application signals, so that we can dispatch the event
                foreach ($this->signalsToDispatchEvent as $signal) {
                    $event = new ConsoleSignalEvent($command, $input, $output, $signal);

                    $this->signalRegistry->register($signal, function ($signal) use ($event, $command, $commandSignals) {
                        $this->dispatcher->dispatch($event, ConsoleEvents::SIGNAL);
                        $exitCode = $event->getExitCode();

                        // If the command is signalable, we call the handleSignal() method
                        if (\in_array($signal, $commandSignals, true)) {
                            $exitCode = $command->handleSignal($signal, $exitCode);
                            // BC layer for Symfony <= 5
                            if (null === $exitCode) {
                                trigger_deprecation('symfony/console', '6.3', 'Not returning an exit code from "%s::handleSignal()" is deprecated, return "false" to keep the command running or "0" to exit successfully.', get_debug_type($command));
                                $exitCode = 0;
                            }
                        }

                        if (false !== $exitCode) {
                            exit($exitCode);
                        }
                    });
                }

                // then we register command signals, but not if already handled after the dispatcher
                $commandSignals = array_diff($commandSignals, $this->signalsToDispatchEvent);
            }

            foreach ($commandSignals as $signal) {
                $this->signalRegistry->register($signal, function (/*int */$signal) use ($command)/*: void*/ {
                    $exitCode = $command->handleSignal($signal);
                    // BC layer for Symfony <= 5
                    if (null === $exitCode) {
                        trigger_deprecation('symfony/console', '6.3', 'Not returning an exit code from "%s::handleSignal()" is deprecated, return "false" to keep the command running or "0" to exit successfully.', get_debug_type($command));
                        $exitCode = 0;
                    }

                    if (false !== $exitCode) {
                        exit($exitCode);
                    }
                });
            }
        }
    }
}
