<?php
namespace PHPec;


/**
 * Logger片段，实现各级别输出接口调用通用输出
 */
Trait LoggerTrait
{
    public function debug($msg, ...$args)
    {
        $this -> log('debug', $msg, ...$args);
    }
    public function info($msg, ...$args)
    {
        $this -> log('info', $msg, ...$args);
    }
    public function event($msg, ...$args)
    {
        $this -> log('event', $msg, ...$args);
    }
    public function notice($msg, ...$args)
    {
        $this -> log('notice', $msg, ...$args);
    }
    public function warning($msg, ...$args)
    {
        $this -> log('warning', $msg, ...$args);
    }
    public function error($msg, ...$args)
    {
        $this -> log('error', $msg, ...$args);
    }
}