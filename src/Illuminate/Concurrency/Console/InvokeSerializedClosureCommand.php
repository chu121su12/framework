<?php

namespace Illuminate\Concurrency\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Throwable;

#[AsCommand(name: 'invoke-serialized-closure')]
class InvokeSerializedClosureCommand extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'invoke-serialized-closure {code? : The serialized closure}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Invoke the given serialized closure';

    /**
     * Indicates whether the command should be shown in the Artisan command list.
     *
     * @var bool
     */
    protected $hidden = true;

    /**
     * Execute the console command.
     *
     * @return void
     *
     * @throws \RuntimeException
     */
    public function handle()
    {
        try {
            switch (true) {
                case ! is_null($this->argument('code')): $toCall = backport_unserialize($this->argument('code')); break;
                case isset($_SERVER['LARAVEL_INVOKABLE_CLOSURE']): $toCall = backport_unserialize($_SERVER['LARAVEL_INVOKABLE_CLOSURE']); break;
                default: $toCall = function () { return null; };
            }

            $this->output->write(backport_json_encode([
                'successful' => true,
                'result' => backport_serialize($this->laravel->call($toCall)),
            ]));
        } catch (\Exception $e) {
        } catch (\ErrorException $e) {
        } catch (Throwable $e) {
        }

        if (isset($e)) {
            report($e);

            $this->output->write(backport_json_encode([
                'successful' => false,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]));
        }
    }
}
