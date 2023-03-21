<?php

namespace Spatie\Ignition\Contracts;

/**
 * Interface used for SolutionProviders.
 */
interface ProvidesSolution
{
    public function getSolution()/*: Solution*/;
}
