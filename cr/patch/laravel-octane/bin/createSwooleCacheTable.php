<?php

use Swoole\Table;

if (isset($serverState) && isset($serverState['octaneConfig']) && isset($serverState['octaneConfig']['cache']) ? $serverState['octaneConfig']['cache'] : false) {
    $cacheTable = new Table(isset($serverState['octaneConfig']['cache']['rows']) ? $serverState['octaneConfig']['cache']['rows'] : 1000);

    $cacheTable->column('value', Table::TYPE_STRING, isset($serverState['octaneConfig']['cache']['bytes']) ? $serverState['octaneConfig']['cache']['bytes'] : 10000);
    $cacheTable->column('expiration', Table::TYPE_INT);

    $cacheTable->create();

    return $cacheTable;
}
