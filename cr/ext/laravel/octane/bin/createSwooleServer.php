<?php

$config = $serverState['octaneConfig'];

try {
    $host = isset($serverState['host']) ? $serverState['host'] : '127.0.0.1';

    $sock = filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? SWOOLE_SOCK_TCP : SWOOLE_SOCK_TCP6;

    $server = new Swoole\Http\Server(
        $host,
        isset($serverState['port']) ? $serverState['port'] : 8000,
        isset($config['swoole']) && isset($config['swoole']['mode']) ? $config['swoole']['mode'] : SWOOLE_PROCESS,
        (isset($config['swoole']) && isset($config['swoole']['ssl']) ? $config['swoole']['ssl'] : false)
            ? $sock | SWOOLE_SSL
            : $sock
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
