<?php

namespace Orchestra\Testbench\Foundation;

use Orchestra\Testbench\Concerns\CreatesApplication;

class ParallelRunner_createApplication_class {
            use CreatesApplication;
        }

class ParallelRunner extends \Illuminate\Testing\ParallelRunner
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Contracts\Foundation\Application
     */
    protected function createApplication()
    {
        $applicationCreator = new ParallelRunner_createApplication_class();

        return $applicationCreator->createApplication();
    }
}
