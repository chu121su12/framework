<?php

#
# Force CI tests to be run.
#
# Usage:
#
#   vendor/bin/phpunit --bootstrap cr/test.php
#

$_SERVER['CI'] = true;

$_SERVER['CI_DB_DRIVER'] = 'mysql';
$_SERVER['CI_DB_HOST'] = '127.0.0.1';
$_SERVER['CI_DB_PORT'] = '3306';
$_SERVER['CI_DB_USERNAME'] = 'forge';
$_SERVER['CI_DB_PASSWORD'] = 'forge';
$_SERVER['CI_DB_DATABASE'] = 'forge';
