<?php

namespace Orchestra\Testbench\Concerns\Database;

use Closure;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\SQLiteBuilder;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Support\Fluent;

class WithSqlite_hotfix_Blueprint extends Blueprint {
                                public function dropForeign($index)
                                {
                                    return new Fluent();
                                }
                            }

class WithSqlite_hotfix_SQLiteBuilder extends SQLiteBuilder {
                        protected function createBlueprint($table, Closure $callback = null)
                        {
                            return new WithSqlite_hotfix_Blueprint($table, $callback);
                        }
                    }

class WithSqlite_hotfix_SQLiteConnection extends SQLiteConnection {
                public function getSchemaBuilder()
                {
                    if ($this->schemaGrammar === null) {
                        $this->useDefaultSchemaGrammar();
                    }

                    return new WithSqlite_hotfix_SQLiteBuilder($this);
                }
            }

trait WithSqlite
{
    /**
     * Add support for SQLite drop foreign.
     *
     * @return void
     */
    protected function hotfixForSqliteSchemaBuilder()////: void
    {
        Connection::resolverFor('sqlite', static function ($connection, $database, $prefix, $config) {
            return new WithSqlite_hotfix_SQLiteConnection($connection, $database, $prefix, $config);
        });
    }
}
