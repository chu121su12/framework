<?php

namespace Illuminate\Testing\Constraints;

use PHPUnit\Framework\Constraint\Constraint;

if (phpunit_major_version() <= 10) {
    class NotSoftDeletedInDatabase extends Constraint
    {
        use Concerns\NotSoftDeletedInDatabase;
    }
} else {
    /*readonly */class NotSoftDeletedInDatabase extends Constraint
    {
        use Concerns\NotSoftDeletedInDatabase;
    }
}
