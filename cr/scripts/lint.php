<?php

$ignore = [
    '~\bcr/patch/symfony/polyfill/~',
    '~\bcr/ext/nesbot/carbon/bootstrapper/CarbonSetStateInterface.php$~',
    '~\bcr/ext/nesbot/carbon/bootstrapper/SetState.php$~',
    '~\btests/Foundation/fixtures/bad-syntax-strategy.php$~',
];

foreach (['cr', 'src', 'tests'] as $dir) {
    $path = sprintf('%s/../../%s/', __DIR__, $dir);
    $options = FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::SKIP_DOTS;
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, $options)
    );

    foreach ($iterator as $fileinfo) {
        $realpath = $fileinfo->getRealPath();

        if (! preg_match('/\.php$/', $realpath)) {
            continue;
        }

        foreach ($ignore as $value) {
            if (preg_match($value, str_replace('\\', '/', $realpath))) {
                continue 2;
            }
        }

        $shell = sprintf('%s -l %s', PHP_BINARY, escapeshellarg($fileinfo->getRealPath()));
        $output = shell_exec($shell);
        if (! preg_match('/^No syntax errors detected in/', $output)) {
            echo $output;
        }
    }
}
