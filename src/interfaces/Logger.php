<?php
namespace PHPec\interfaces;

interface Logger
{
    /**
     * 输出各级别日志，参数与printf一样，支持占位
     */
    public function debug($msg, ...$args);
    public function info($msg, ...$args);
    public function event($msg, ...$args);
    public function notice($msg, ...$args);
    public function warning($msg, ...$args);
    public function error($msg, ...$args);
    /**
     * 输出指定类别日志
     */
    public function log($level, $msg, ...$args);
}