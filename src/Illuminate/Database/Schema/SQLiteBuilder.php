<?php

namespace Illuminate\Database\Schema;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\File;

class SQLiteBuilder extends Builder
{
    /**
     * Create a database in the schema.
     *
     * @param  string  $name
     * @return bool
     */
    public function createDatabase($name)
    {
        return File::put($name, '') !== false;
    }

    /**
     * Drop a database from the schema if the database exists.
     *
     * @param  string  $name
     * @return bool
     */
    public function dropDatabaseIfExists($name)
    {
        return File::exists($name)
            ? File::delete($name)
            : true;
    }

    /**
     * Get the tables for the database.
     *
     * @return array
     */
    public function getTables()
    {
        $withSize = false;

        try {
            $withSize = $this->connection->scalar($this->grammar->compileDbstatExists());
        } catch (QueryException $_e) {
            //
        }

        return $this->connection->getPostProcessor()->processTables(
            $this->connection->selectFromWriteConnection($this->grammar->compileTables($withSize))
        );
    }

    /**
     * Get the columns for a given table.
     *
     * @param  string  $table
     * @return array
     */
    public function getColumns($table)
    {
        $table = $this->connection->getTablePrefix().$table;

        return $this->connection->getPostProcessor()->processColumns(
            $this->getColumnsPragma($table) ?: $this->connection->selectFromWriteConnection($this->grammar->compileColumns($table)),
            $this->connection->scalar($this->grammar->compileSqlCreateStatement($table))
        );
    }

    /**
     * Get all of the table names for the database.
     *
     * @deprecated Will be removed in a future Laravel version.
     *
     * @return array
     */
    public function getAllTables()
    {
        return $this->connection->select(
            $this->grammar->compileGetAllTables()
        );
    }

    /**
     * Get all of the view names for the database.
     *
     * @deprecated Will be removed in a future Laravel version.
     *
     * @return array
     */
    public function getAllViews()
    {
        return $this->connection->select(
            $this->grammar->compileGetAllViews()
        );
    }

    /**
     * Drop all tables from the database.
     *
     * @return void
     */
    public function dropAllTables()
    {
        if ($this->connection->getDatabaseName() !== ':memory:') {
            return $this->refreshDatabaseFile();
        }

        $this->connection->select($this->grammar->compileEnableWriteableSchema());

        $this->connection->select($this->grammar->compileDropAllTables());

        $this->connection->select($this->grammar->compileDisableWriteableSchema());

        $this->connection->select($this->grammar->compileRebuild());
    }

    /**
     * Drop all views from the database.
     *
     * @return void
     */
    public function dropAllViews()
    {
        $this->connection->select($this->grammar->compileEnableWriteableSchema());

        $this->connection->select($this->grammar->compileDropAllViews());

        $this->connection->select($this->grammar->compileDisableWriteableSchema());

        $this->connection->select($this->grammar->compileRebuild());
    }

    /**
     * Empty the database file.
     *
     * @return void
     */
    public function refreshDatabaseFile()
    {
        file_put_contents($this->connection->getDatabaseName(), '');
    }

    protected function getColumnsPragma($table)
    {
        if (! _data_get($this->connection, 'legacySupport')) {
            return null;
        }

        $table = $this->grammar->wrap(str_replace('.', '__', $table));

        $columns = $this->connection->selectFromWriteConnection('pragma table_info('.$table.')');

        return (new \Illuminate\Support\Collection($columns))
            ->map(function ($row) {
                return (object) [
                    'name' => $row->name,
                    'type' => $row->type,
                    'nullable' => ! $row->notnull,
                    'default' => $row->dflt_value,
                    'primary' => $row->pk,
                ];
            })
            ->sortBy('cid')
            ->values()
            ->all();
    }

    public function getIndexes($table)
    {
        $table = $this->connection->getTablePrefix().$table;

        return $this->connection->getPostProcessor()->processIndexes(
            $this->connection->selectFromWriteConnection($this->grammar->compileIndexes($table))
        );
    }
}
