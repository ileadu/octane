<?php

$config = $serverState['octaneConfig'];

try {
    if (str_starts_with($serverState['host'], '/')) {
        $server = new Swoole\Http\Server(
            $serverState['host'],  // Use unix domain socket
            0,          // Port is not needed
            $config['swoole']['mode'] ?? SWOOLE_PROCESS,
            SWOOLE_SOCK_UNIX_STREAM
        );
    } else {
        $host = $serverState['host'] ?? '127.0.0.1';
        $sock = filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? SWOOLE_SOCK_TCP : SWOOLE_SOCK_TCP6;
        $server = new Swoole\Http\Server(
            $host,
            $serverState['port'] ?? 8000,
            $config['swoole']['mode'] ?? SWOOLE_PROCESS,
            ($config['swoole']['ssl'] ?? false)
                ? $sock | SWOOLE_SSL
                : $sock,
        );
    }
} catch (Throwable $e) {
    Laravel\Octane\Stream::shutdown($e);

    exit(1);
}

$server->set(array_merge(
    $serverState['defaultServerOptions'],
    $config['swoole']['options'] ?? []
));

return $server;
