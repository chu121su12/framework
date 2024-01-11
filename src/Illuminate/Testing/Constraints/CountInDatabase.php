<?php

namespace Illuminate\Testing\Constraints;

use PHPUnit\Framework\Constraint\Constraint;

if (phpunit_major_version() <= 10) {
    class CountInDatabase extends Constraint
    {
        use Concerns\CountInDatabase;
    }
} else {
    /*readonly */class CountInDatabase extends Constraint
    {
        use Concerns\CountInDatabase;
    }
}
