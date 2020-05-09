<?php

namespace Illuminate\Database\Query\Processors;

class MySqlProcessor extends Processor
{
    /**
     * Process the results of a column listing query.
     *
     * @param  array  $results
     * @return array
     */
    public function processColumnListing($results)
    {
        return array_map(function ($result) {
            $object = (object) $result;
            return $object->column_name;
        }, $results);
    }
}
