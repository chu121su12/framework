<?php

namespace Illuminate\Testing\Constraints;

use PHPUnit\Framework\Constraint\Constraint;

if (phpunit_major_version() <= 10) {
    class SeeInOrder extends Constraint
    {
        use Concerns\SeeInOrder;
    }
} else {
    /*readonly */class SeeInOrder extends Constraint
    {
        use Concerns\SeeInOrder;
    }
}
