<?php
namespace PHPec\connections;

class Redis extends Base
{
    //建立连接
    public function conn($target, $persistent)
    {
        if (empty($target['host'])) trigger_error("Redis Error: miss connect param", E_USER_ERROR);
        @list($host, $port) = explode(":", $target['host']);
        $port = empty($port) ? 6379 : $port;
        $redis = new \Redis();
        if (!method_exists($redis, 'connect')) trigger_error("Redis extendsion not installed", E_USER_ERROR);
        if ($persistent) $redis -> pconnect($host, $port);
        else $redis -> connect($host, $port);
        $redis -> connect($host, $port);
        if (!empty($target['auth'])) {
            $redis -> auth($target['auth']);
        }
        return $redis;
    }
}