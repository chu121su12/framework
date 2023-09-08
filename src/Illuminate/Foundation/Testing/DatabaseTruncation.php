<?php

namespace Illuminate\Foundation\Testing;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Foundation\Testing\Traits\CanConfigureMigrationCommands;

trait DatabaseTruncation
{
    use CanConfigureMigrationCommands;

    /**
     * The cached names of the database tables for each connection.
     *
     * @var array
     */
    protected static /*array */$allTables;

    /**
     * Truncate the database tables for all configured connections.
     *
     * @return void
     */
    protected function truncateDatabaseTables()/*: void*/
    {
        // Migrate and seed the database on first run...
        if (! RefreshDatabaseState::$migrated) {
            $this->artisan('migrate:fresh', $this->migrateFreshUsing());

            $this->app[Kernel::class]->setArtisan(null);

            RefreshDatabaseState::$migrated = true;

            return;
        }

        // Always clear any test data on subsequent runs...
        $this->truncateTablesForAllConnections();

        if ($seeder = $this->seeder()) {
            // Use a specific seeder class...
            $this->artisan('db:seed', ['--class' => $seeder]);
        } elseif ($this->shouldSeed()) {
            // Use the default seeder class...
            $this->artisan('db:seed');
        }
    }

    /**
     * Truncate the database tables for all configured connections.
     *
     * @return void
     */
    protected function truncateTablesForAllConnections()/*: void*/
    {
        $database = $this->app->make('db');

        collect($this->connectionsToTruncate())
            ->each(function ($name) use ($database) {
                $connection = $database->connection($name);

                $connection->getSchemaBuilder()->withoutForeignKeyConstraints(
                    function () use ($connection, $name) {
                        return $this->truncateTablesForConnection($connection, $name);
                    }
                );
            });
    }

    /**
     * Truncate the database tables for the given database connection.
     *
     * @param  \Illuminate\Database\ConnectionInterface  $connection
     * @param  string|null  $name
     * @return void
     */
    protected function truncateTablesForConnection(ConnectionInterface $connection, /*?string */$name = null)/*: void*/
    {
        $name = backport_type_check('?string', $name);

        $dispatcher = $connection->getEventDispatcher();

        $connection->unsetEventDispatcher();

        if (! isset(static::$allTables[$name])) {
            static::$allTables[$name] = $connection->getDoctrineSchemaManager()->listTableNames();
        }

        collect(static::$allTables[$name])
            ->when(
                property_exists($this, 'tablesToTruncate'),
                function ($tables) { return $tables->intersect($this->tablesToTruncate); },
                function ($tables) { return $tables->diff($this->exceptTables($name)); }
            )
            ->filter(fn ($table) => $connection->table($this->withoutTablePrefix($connection, $table))->exists())
            ->each(fn ($table) => $connection->table($this->withoutTablePrefix($connection, $table))->truncate());

        $connection->setEventDispatcher($dispatcher);
    }

    /**
     * Remove the table prefix from a table name, if it exists.
     *
     * @param  \Illuminate\Database\ConnectionInterface  $connection
     * @param  string  $table
     * @return string
     */
    protected function withoutTablePrefix(ConnectionInterface $connection, string $table)
    {
        $prefix = $connection->getTablePrefix();

        return strpos($table, $prefix) === 0
            ? substr($table, strlen($prefix))
            : $table;
    }

    /**
     * The database connections that should have their tables truncated.
     *
     * @return array
     */
    protected function connectionsToTruncate()/*: array*/
    {
        return property_exists($this, 'connectionsToTruncate')
                    ? $this->connectionsToTruncate : [null];
    }

    /**
     * Get the tables that should not be truncated.
     *
     * @param  string|null  $connectionName
     * @return array
     */
    protected function exceptTables(/*?string */$connectionName = null)/*: array*/
    {
        $connectionName = backport_type_check('?string', $connectionName);

        if (property_exists($this, 'exceptTables')) {
            $migrationsTable = $this->app['config']->get('database.migrations');

            if (array_is_list(isset($this->exceptTables) ? $this->exceptTables : [])) {
                return array_merge(
                    isset($this->exceptTables) ? $this->exceptTables : [],
                    [$migrationsTable]
                );
            }

            return array_merge(
                isset($this->exceptTables[$connectionName]) ? $this->exceptTables[$connectionName] : [],
                [$migrationsTable]
            );
        }

        return [$this->app['config']->get('database.migrations')];
    }
}
