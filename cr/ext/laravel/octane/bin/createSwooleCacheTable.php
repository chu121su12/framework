<?php

use Laravel\Octane\Tables\TableFactory;
use Swoole\Table;

require_once __DIR__.'/../src/Tables/TableFactory.php';

if ($isset($serverState) && isset($serverState['octaneConfig']) && isset($serverState['octaneConfig']['cache']) ? $serverState['octaneConfig']['cache'] : false) {
    $cacheTable = TableFactory::make(
        isset($serverState['octaneConfig']['cache']['rows']) ? $serverState['octaneConfig']['cache']['rows'] : 1000
    );

    $cacheTable->column('value', Table::TYPE_STRING, isset($serverState['octaneConfig']['cache']['bytes']) ? $serverState['octaneConfig']['cache']['bytes'] : 10000);
    $cacheTable->column('expiration', Table::TYPE_INT);

    $cacheTable->create();

    return $cacheTable;
}
