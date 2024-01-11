<?php

namespace Illuminate\Testing\Constraints;

use PHPUnit\Framework\Constraint\Constraint;

if (phpunit_major_version() <= 10) {
    class ArraySubset extends Constraint
    {
        use Concerns\ArraySubset;
    }
} else {
    /*readonly */class ArraySubset extends Constraint
    {
        use Concerns\ArraySubset;
    }
}
