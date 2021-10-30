<?php

#
# Force CI tests to be run.
#
# Database:
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

$_SERVER['CI'] = true;

$_SERVER['CI_DB_DRIVER'] = 'mysql';
$_SERVER['CI_DB_HOST'] = '127.0.0.1';
$_SERVER['CI_DB_PORT'] = '3306';
$_SERVER['CI_DB_USERNAME'] = 'forge';
$_SERVER['CI_DB_PASSWORD'] = 'forge';
$_SERVER['CI_DB_DATABASE'] = 'forge';

// $_SERVER['CI_FORCE_DATABASE'] = true;

define('__TEST_START__', microtime(true));
register_shutdown_function(function () {
	$finished = microtime(true);

	$timeFormat = function ($timestamp) {
		$date = new DateTime();
		$date->setTimeStamp($timestamp);
		return $date->format('G:i:s') . substr($timestamp, strpos($timestamp, '.'));
	};

	echo sprintf(
		'Time Start (%s): %s, Finished: %s, Elapsed: %ss.',
		date_default_timezone_get(),
		$timeFormat(__TEST_START__),
		$timeFormat($finished),
		round($finished - __TEST_START__, 3)
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
