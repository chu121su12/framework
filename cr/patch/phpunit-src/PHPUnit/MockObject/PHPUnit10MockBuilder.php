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
        return $this->setMethods(is_array($methods) && count($methods) ? $methods : null);
    }

    public function addMethods(array $methods = null)
    {
        return $this->setMethods(array_merge($this->phpUnit10MockBuilderMethods, $methods));
    }
}
