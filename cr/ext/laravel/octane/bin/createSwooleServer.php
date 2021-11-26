<?php

$config = $serverState['octaneConfig'];

try {
    $e = null;

    $server = new Swoole\Http\Server(
        isset($serverState['host']) ? $serverState['host'] : '127.0.0.1',
        isset($serverState['port']) ? $serverState['port'] : '8080',
        SWOOLE_PROCESS,
        (isset($config['swoole']) && isset($config['swoole']['ssl']) ? $config['swoole']['ssl'] : false)
            ? SWOOLE_SOCK_TCP | SWOOLE_SSL
            : SWOOLE_SOCK_TCP
    );
} catch (Exception $e) {
} catch (Error $e) {
} catch (Throwable $e) {
}

if (isset($e)) {
    Laravel\Octane\Stream::shutdown($e);

    exit(1);
}

$server->set(array_merge(
    $serverState['defaultServerOptions'],
    isset($config['swoole']) && isset($config['swoole']['options']) ? $config['swoole']['options'] : []
));

return $server;
