<?php

namespace Orchestra\Testbench\Concerns\Database;

use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\SQLiteBuilder;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Support\Fluent;

class WithSqlite_hotfixForSqliteSchemaBuilder_class_inner_2 extends Blueprint {
                                public function dropForeign($index)
                                {
                                    return new Fluent();
                                }
                            }

class WithSqlite_hotfixForSqliteSchemaBuilder_class_inner_1 extends SQLiteBuilder {
                        protected function createBlueprint($table, Closure $callback = null)
                        {
                            return new WithSqlite_hotfixForSqliteSchemaBuilder_class_inner_2($table, $callback);
                        }
                    }

class WithSqlite_hotfixForSqliteSchemaBuilder_class extends SQLiteConnection {
                public function getSchemaBuilder()
                {
                    if ($this->schemaGrammar === null) {
                        $this->useDefaultSchemaGrammar();
                    }

                    return new WithSqlite_hotfixForSqliteSchemaBuilder_class_inner_1($this);
                }
            }

trait WithSqlite
{
    /**
     * Add support for SQLite drop foreign.
     *
     * @return void
     */
    protected function hotfixForSqliteSchemaBuilder()
    {
        Connection::resolverFor('sqlite', static function ($connection, $database, $prefix, $config) {
            return new WithSqlite_hotfixForSqliteSchemaBuilder_class($connection, $database, $prefix, $config);
        });
    }
}
