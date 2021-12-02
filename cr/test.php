<?php

#
# Force CI tests to be run.
#
# MySql Database:
#
#   CREATE DATABASE `forge` COLLATE 'utf8mb4_unicode_ci';
#   CREATE USER 'forge'@'127.0.0.1' IDENTIFIED BY 'forge';
#   GRANT ALL PRIVILEGES ON `forge`.* TO 'forge'@'127.0.0.1';
#
#   DROP USER 'forge'@'*'
#   DROP DATABASE `forge`;
#
# Usage:
#
#   cr/phpunit --bootstrap cr/test.php
#   vendor/bin/phpunit --bootstrap cr/test.php
#

$_SERVER['CI_DB_AUTH_DRIVER'] = 'mysql';
$_SERVER['CI_DB_MYSQL_PORT'] = '3306';

$_SERVER['CI_DB_HOST'] = call_user_func(function ($file) {
	return file_exists($file) && ($ip = trim(file_get_contents($file))) ? $ip : '127.0.0.1';
}, __DIR__.'/ip.text');

$_SERVER['CI_DB_USERNAME'] = 'forge';
$_SERVER['CI_DB_PASSWORD'] = 'forge';
$_SERVER['CI_DB_DATABASE'] = 'forge';
$_SERVER['CI_DB_OPTIONS_TIMEOUT'] = 10;

define('__TEST_START__', microtime(true));
register_shutdown_function(function () {
	$finished = microtime(true);

	$timeFormat = function ($timestamp) {
		$date = new DateTime();
		$date->setTimeStamp((int) $timestamp);
		return $date->format('G:i:s') . substr($timestamp, strpos($timestamp, '.'));
	};

	echo sprintf(
		'Start (%s): %s, Finished: %s, Elapsed: %ss.%s',
		date_default_timezone_get(),
		$timeFormat(__TEST_START__),
		$timeFormat($finished),
		round($finished - __TEST_START__, 3),
		PHP_EOL
	);

	if (false && file_exists($testbenchExcessDir = __DIR__ . '/patch/orchestra-testbench-core/testbench-core/laravel')) {
		foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(
			$testbenchExcessDir, RecursiveDirectoryIterator::SKIP_DOTS
		), RecursiveIteratorIterator::CHILD_FIRST) as $file) {
			call_user_func($file->isDir() ? 'rmdir' : 'unlink', $file->getRealPath());
		}
		rmdir($testbenchExcessDir);
	}
});
