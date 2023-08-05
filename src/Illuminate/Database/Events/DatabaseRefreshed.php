<?php

namespace Illuminate\Database\Events;

use Illuminate\Contracts\Database\Events\MigrationEvent as MigrationEventContract;

class DatabaseRefreshed implements MigrationEventContract
{
    public /*?string */$database;
    public /*bool */$seeding;

    /**
     * Create a new event instance.
     *
     * @param  string|null  $database
     * @param  bool  seeding
     * @return void
     */
    public function __construct(
        /*public ?string */$database = null,
        /*public bool */$seeding = false
    ) {
        $database = backport_type_check('?string', $database);
        $seeding = backport_type_check('bool', $seeding);

        //
    }
}
