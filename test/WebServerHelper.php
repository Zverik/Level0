<?php

class WebServerHelper
{
    private static $pid;
    private static $host = 'localhost';
    private static $port = 8000;
    private static $docRoot = __DIR__ . '/../www';

    public static function url()
    {
    	return 'http://' . self::$host . ':' . self::$port;
    }

    public static function start()
    {
        $command = sprintf(
            'php -S %s:%d -t %s > /dev/null 2>&1 & echo $!',
            self::$host,
            self::$port,
            escapeshellarg(self::$docRoot)
        );

        $output = [];
        exec($command, $output);
        self::$pid = (int) $output[0];

        file_put_contents(__DIR__ . '/server.pid', self::$pid);
        sleep(1);
    }

    public static function stop()
    {
        $pid = (int) @file_get_contents(__DIR__ . '/server.pid');
        if ($pid) {
            exec('kill ' . $pid);
            unlink(__DIR__ . '/server.pid');
        }
    }
}
