<?php

namespace Illuminate\Database\Console;

use CR\LaravelBackport\SymfonyHelper;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Illuminate\Console\Command;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\MySqlConnection;
use Illuminate\Database\PostgresConnection;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Database\SqlServerConnection;
use Illuminate\Support\Arr;
use Illuminate\Support\Composer;
use Symfony\Component\Process\Exception\ProcessSignaledException;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;

abstract class DatabaseInspectionCommand extends Command
{
    /**
     * A map of database column types.
     *
     * @var array
     */
    protected $typeMappings = [
        'bit' => 'string',
        'enum' => 'string',
        'geometry' => 'string',
        'geomcollection' => 'string',
        'linestring' => 'string',
        'multilinestring' => 'string',
        'multipoint' => 'string',
        'multipolygon' => 'string',
        'point' => 'string',
        'polygon' => 'string',
        'sysname' => 'string',
    ];

    /**
     * The Composer instance.
     *
     * @var \Illuminate\Support\Composer
     */
    protected $composer;

    /**
     * Create a new command instance.
     *
     * @param  \Illuminate\Support\Composer|null  $composer
     * @return void
     */
    public function __construct(Composer $composer = null)
    {
        parent::__construct();

        $this->composer = isset($composer) ? $composer : $this->laravel->make(Composer::class);
    }

    /**
     * Register the custom Doctrine type mappings for inspection commands.
     *
     * @param  \Doctrine\DBAL\Platforms\AbstractPlatform  $platform
     * @return void
     */
    protected function registerTypeMappings(AbstractPlatform $platform)
    {
        foreach ($this->typeMappings as $type => $value) {
            $platform->registerDoctrineTypeMapping($type, $value);
        }
    }

    /**
     * Get a human-readable platform name for the given platform.
     *
     * @param  \Doctrine\DBAL\Platforms\AbstractPlatform  $platform
     * @param  string  $database
     * @return string
     */
    protected function getPlatformName(AbstractPlatform $platform, $database)
    {
        switch (class_basename($platform)) {
            case 'MySQLPlatform': return 'MySQL <= 5';
            case 'MySQL57Platform': return 'MySQL 5.7';
            case 'MySQL80Platform': return 'MySQL 8';
            case 'PostgreSQL100Platform':
            case 'PostgreSQLPlatform': return 'Postgres';
            case 'SqlitePlatform': return 'SQLite';
            case 'SQLServerPlatform': return 'SQL Server';
            case 'SQLServer2012Platform': return 'SQL Server 2012';
            default: return $database;
        }
    }

    /**
     * Get the size of a table in bytes.
     *
     * @param  \Illuminate\Database\ConnectionInterface  $connection
     * @param  string  $table
     * @return int|null
     */
    protected function getTableSize(ConnectionInterface $connection, /*string */$table)
    {
        $table = backport_type_check('string', $table);

        switch (true) {
            case $connection instanceof MySqlConnection: return $this->getMySQLTableSize($connection, $table);
            case $connection instanceof PostgresConnection: return $this->getPostgresTableSize($connection, $table);
            case $connection instanceof SQLiteConnection: return $this->getSqliteTableSize($connection, $table);
            default: return null;
        }
    }

    /**
     * Get the size of a MySQL table in bytes.
     *
     * @param  \Illuminate\Database\ConnectionInterface  $connection
     * @param  string  $table
     * @return mixed
     */
    protected function getMySQLTableSize(ConnectionInterface $connection, /*string */$table)
    {
        $table = backport_type_check('string', $table);

        $result = $connection->selectOne('SELECT (data_length + index_length) AS size FROM information_schema.TABLES WHERE table_schema = ? AND table_name = ?', [
            $connection->getDatabaseName(),
            $table,
        ]);

        return Arr::wrap((array) $result)['size'];
    }

    /**
     * Get the size of a Postgres table in bytes.
     *
     * @param  \Illuminate\Database\ConnectionInterface  $connection
     * @param  string  $table
     * @return mixed
     */
    protected function getPostgresTableSize(ConnectionInterface $connection, /*string */$table)
    {
        $table = backport_type_check('string', $table);

        $result = $connection->selectOne('SELECT pg_total_relation_size(?) AS size;', [
            $table,
        ]);

        return Arr::wrap((array) $result)['size'];
    }

    /**
     * Get the size of a SQLite table in bytes.
     *
     * @param  \Illuminate\Database\ConnectionInterface  $connection
     * @param  string  $table
     * @return mixed
     */
    protected function getSqliteTableSize(ConnectionInterface $connection, /*string */$table)
    {
        $table = backport_type_check('string', $table);

        $result = $connection->selectOne('SELECT SUM(pgsize) AS size FROM dbstat WHERE name=?', [
            $table,
        ]);

        return Arr::wrap((array) $result)['size'];
    }

    /**
     * Get the number of open connections for a database.
     *
     * @param  \Illuminate\Database\ConnectionInterface  $connection
     * @return int|null
     */
    protected function getConnectionCount(ConnectionInterface $connection)
    {
        $result = null;
        switch (true) {
            case $connection instanceof MySqlConnection:
                $result = $connection->selectOne('show status where variable_name = "threads_connected"');
                break;

            case $connection instanceof PostgresConnection:
                $result = $connection->selectOne('select count(*) AS "Value" from pg_stat_activity');
                break;

            case $connection instanceof SqlServerConnection:
                $result = $connection->selectOne('SELECT COUNT(*) Value FROM sys.dm_exec_sessions WHERE status = ?', ['running']);
                break;
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
     * Ensure the dependencies for the database commands are available.
     *
     * @return bool
     */
    protected function ensureDependenciesExist()
    {
        return tap(interface_exists('Doctrine\DBAL\Driver'), function ($dependenciesExist) {
            if (! $dependenciesExist && $this->components->confirm('Inspecting database information requires the Doctrine DBAL (doctrine/dbal) package. Would you like to install it?')) {
                $this->installDependencies();
            }
        });
    }

    /**
     * Install the command's dependencies.
     *
     * @return void
     *
     * @throws \Symfony\Component\Process\Exception\ProcessSignaledException
     */
    protected function installDependencies()
    {
        $command = collect($this->composer->findComposer())
            ->push('require doctrine/dbal')
            ->implode(' ');

        $process = SymfonyHelper::processFromShellCommandline($command, null, null, null, null);

        if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
            try {
                $process->setTty(true);
            } catch (RuntimeException $e) {
                $this->components->warn($e->getMessage());
            }
        }

        try {
            $process->run(function ($type, $line) { return $this->output->write($line); });
        } catch (ProcessSignaledException $e) {
            if (extension_loaded('pcntl') && $e->getSignal() !== SIGINT) {
                throw $e;
            }
        }
    }
}
