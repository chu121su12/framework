<?php

namespace Doctrine\Inflector;

use Doctrine\Common\Inflector\Inflector as DoctrineCommonInflector;

class DepracatedInflector
{
    public function pluralize($value)
    {
        return DoctrineCommonInflector::pluralize($value);
    }

    public function singularize($value)
    {
        return DoctrineCommonInflector::singularize($value);
    }
}
