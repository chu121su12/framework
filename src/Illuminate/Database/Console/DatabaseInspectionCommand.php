<?php

namespace Illuminate\Database\Console;

use CR\LaravelBackport\SymfonyHelper;
use Illuminate\Console\Command;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\MySqlConnection;
use Illuminate\Database\PostgresConnection;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Database\SqlServerConnection;
use Illuminate\Support\Arr;

abstract class DatabaseInspectionCommand extends Command
{
    /**
     * Get a human-readable name for the given connection.
     *
     * @param  \Illuminate\Database\ConnectionInterface  $connection
     * @param  string  $database
     * @return string
     */
    protected function getConnectionName(ConnectionInterface $connection, $database)
    {
        switch (true) {
            case $connection instanceof MySqlConnection && $connection->isMaria(): return 'MariaDB';
            case $connection instanceof MySqlConnection: return 'MySQL';
            case $connection instanceof PostgresConnection: return 'PostgreSQL';
            case $connection instanceof SQLiteConnection: return 'SQLite';
            case $connection instanceof SqlServerConnection: return 'SQL Server';
            default: return $database;
        }
    }

    /**
     * Get the number of open connections for a database.
     *
     * @param  \Illuminate\Database\ConnectionInterface  $connection
     * @return int|null
     */
    protected function getConnectionCount(ConnectionInterface $connection)
    {
        switch (true) {
            case $connection instanceof MySqlConnection: $result = $connection->selectOne('show status where variable_name = "threads_connected"'); break;
            case $connection instanceof PostgresConnection: $result = $connection->selectOne('select count(*) as "Value" from pg_stat_activity'); break;
            case $connection instanceof SqlServerConnection: $result = $connection->selectOne('select count(*) Value from sys.dm_exec_sessions where status = ?', ['running']); break;
            default: $result = null;
        }

        if (! $result) {
            return null;
        }

        return Arr::wrap((array) $result)['Value'];
    }

    /**
     * Get the connection configuration details for the given connection.
     *
     * @param  string  $database
     * @return array
     */
    protected function getConfigFromDatabase($database)
    {
        $database = isset($database) ? $database : config('database.default');

        return Arr::except(config('database.connections.'.$database), ['password']);
    }

    /**
     * Remove the table prefix from a table name, if it exists.
     *
     * @param  \Illuminate\Database\ConnectionInterface  $connection
     * @param  string  $table
     * @return string
     */
    protected function withoutTablePrefix(ConnectionInterface $connection, /*string */$table)
    {
        $table = backport_type_check('string', $table);

        $prefix = $connection->getTablePrefix();

        return str_starts_with($table, $prefix)
            ? substr($table, strlen($prefix))
            : $table;
    }
}
