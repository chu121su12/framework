<?php

namespace PHPUnit\MockObject;

class PHPUnit10MockBuilder extends \PHPUnit_Framework_MockObject_MockBuilder
{
    private $phpUnit10MockBuilderMethods = [];

    public function setMethods(array $methods = null)
    {
        return parent::setMethods($this->phpUnit10MockBuilderMethods = $methods);
    }

    public function onlyMethods(array $methods = null)
    {
        return $this->setMethods(count($methods) ? $methods : null);
    }

    public function addMethods(array $methods)
    {
        return $this->setMethods(array_merge($this->phpUnit10MockBuilderMethods, $methods));
    }
}
