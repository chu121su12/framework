<?php

namespace Illuminate\Testing\Constraints;

use PHPUnit\Framework\Constraint\Constraint;

if (phpunit_major_version() <= 10) {
    class HasInDatabase extends Constraint
    {
        use Concerns\HasInDatabase;
    }
} else {
    /*readonly */class HasInDatabase extends Constraint
    {
        use Concerns\HasInDatabase;
    }
}
