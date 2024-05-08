<?php

use Laravel\Octane\Tables\TableFactory;
use Swoole\Table;

require_once __DIR__.'/../src/Tables/TableFactory.php';

$tables = [];

foreach (isset($serverState) && isset($serverState['octaneConfig']) && isset($serverState['octaneConfig']['tables']) ? $serverState['octaneConfig']['tables'] : [] as $name => $columns) {
    $explodedName = explode(':', $name);

    $table = TableFactory::make(isset($explodedName[1]) ? $explodedName[1] : 1000);

    foreach (isset($columns) ? $columns : [] as $columnName => $column) {
        $explodedColumn = explode(':', $column);

        $table->column($columnName, backport_match(
            isset($explodedColumn[0]) ? $explodedColumn[0] : 'string',
            ['string', Table::TYPE_STRING],
            ['int', Table::TYPE_INT],
            ['float', Table::TYPE_FLOAT]
        ), isset($explodedColumn[1]) ? $explodedColumn[1] : 1000);
    }

    $table->create();

    $tables[$explodedName[0]] = $table;
}

return $tables;
