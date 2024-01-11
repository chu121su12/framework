<?php

namespace Illuminate\Testing\Constraints;

use PHPUnit\Framework\Constraint\Constraint;

if (phpunit_major_version() <= 10) {
    class SoftDeletedInDatabase extends Constraint
    {
        use Concerns\SoftDeletedInDatabase;
    }
} else {
    /*readonly */class SoftDeletedInDatabase extends Constraint
    {
        use Concerns\SoftDeletedInDatabase;
    }
}
