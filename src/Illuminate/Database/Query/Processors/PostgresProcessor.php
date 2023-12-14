<?php

namespace Illuminate\Database\Query\Processors;

use Illuminate\Database\Query\Builder;

class PostgresProcessor extends Processor
{
    /**
     * Process an "insert get ID" query.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  string  $sql
     * @param  array  $values
     * @param  string|null  $sequence
     * @return int
     */
    public function processInsertGetId(Builder $query, $sql, $values, $sequence = null)
    {
        $connection = $query->getConnection();

        $connection->recordsHaveBeenModified();

        $result = $connection->selectFromWriteConnection($sql, $values)[0];

        $sequence = $sequence ?: 'id';

        $id = is_object($result) ? $result->{$sequence} : $result[$sequence];

        return backport_is_numeric($id) ? (int) $id : $id;
    }

    /**
     * Process the results of a column listing query.
     *
     * @deprecated Will be removed in a future Laravel version.
     *
     * @param  array  $results
     * @return array
     */
    public function processColumnListing($results)
    {
        return array_map(function ($result) {
            return with((object) $result)->column_name;
        }, $results);
    }

    /**
     * Process the results of a types query.
     *
     * @param  array  $results
     * @return array
     */
    public function processTypes($results)
    {
        return array_map(function ($result) {
            $result = (object) $result;

            return [
                'name' => $result->name,
                'schema' => $result->schema,
                'implicit' => (bool) $result->implicit,
                'type' => call_user_func(function () use ($result) { switch (strtolower($result->type)) {
                    case 'b': return 'base';
                    case 'c': return 'composite';
                    case 'd': return 'domain';
                    case 'e': return 'enum';
                    case 'p': return 'pseudo';
                    case 'r': return 'range';
                    case 'm': return 'multirange';
                    default: return null;
                }}),
                'category' => call_user_func(function () use ($result) { switch (strtolower($result->category)) {
                    case 'a': return 'array';
                    case 'b': return 'boolean';
                    case 'c': return 'composite';
                    case 'd': return 'date_time';
                    case 'e': return 'enum';
                    case 'g': return 'geometric';
                    case 'i': return 'network_address';
                    case 'n': return 'numeric';
                    case 'p': return 'pseudo';
                    case 'r': return 'range';
                    case 's': return 'string';
                    case 't': return 'timespan';
                    case 'u': return 'user_defined';
                    case 'v': return 'bit_string';
                    case 'x': return 'unknown';
                    case 'z': return 'internal_use';
                    default: return null;
                }}),
            ];
        }, $results);
    }

    /**
     * Process the results of a columns query.
     *
     * @param  array  $results
     * @return array
     */
    public function processColumns($results)
    {
        return array_map(function ($result) {
            $result = (object) $result;

            $autoincrement = $result->default !== null && str_starts_with($result->default, 'nextval(');

            return [
                'name' => str_starts_with($result->name, '"') ? str_replace('"', '', $result->name) : $result->name,
                'type_name' => $result->type_name,
                'type' => $result->type,
                'collation' => $result->collation,
                'nullable' => (bool) $result->nullable,
                'default' => $autoincrement ? null : $result->default,
                'auto_increment' => $autoincrement,
                'comment' => $result->comment,
            ];
        }, $results);
    }

    /**
     * Process the results of an indexes query.
     *
     * @param  array  $results
     * @return array
     */
    public function processIndexes($results)
    {
        return array_map(function ($result) {
            $result = (object) $result;

            return [
                'name' => strtolower($result->name),
                'columns' => explode(',', $result->columns),
                'type' => strtolower($result->type),
                'unique' => (bool) $result->unique,
                'primary' => (bool) $result->primary,
            ];
        }, $results);
    }

    /**
     * Process the results of a foreign keys query.
     *
     * @param  array  $results
     * @return array
     */
    public function processForeignKeys($results)
    {
        return array_map(function ($result) {
            $result = (object) $result;

            return [
                'name' => $result->name,
                'columns' => explode(',', $result->columns),
                'foreign_schema' => $result->foreign_schema,
                'foreign_table' => $result->foreign_table,
                'foreign_columns' => explode(',', $result->foreign_columns),
                'on_update' => call_user_func(function () use ($result) { switch (strtolower($result->on_update)) {
                    case 'a': return 'no action';
                    case 'r': return 'restrict';
                    case 'c': return 'cascade';
                    case 'n': return 'set null';
                    case 'd': return 'set default';
                    default: return null;
                }} ),
                'on_delete' => call_user_func(function () use ($result) { switch (strtolower($result->on_delete)) {
                    case 'a': return 'no action';
                    case 'r': return 'restrict';
                    case 'c': return 'cascade';
                    case 'n': return 'set null';
                    case 'd': return 'set default';
                    default: return null;
                }} ),
            ];
        }, $results);
    }
}
