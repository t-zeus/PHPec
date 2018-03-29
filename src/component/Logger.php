<?php
namespace PHPec\component;

class Logger implements \PHPec\interfaces\Logger
{
    use \PHPec\DITrait;
    use \PHPec\LoggerTrait;

    private $levels = [
        'debug'     => 1,
        'info'      => 2,
        'event'     => 4,
        'notice'    => 8,
        'warning'   => 16,
        'error'     => 32
    ];

    private $all = 1 << 6 - 1;
     
    public function log($level, $msg, ...$args)
    {
        if (!isset($this -> levels[$level])) {
            trigger_error('Logger level error --'.$level, E_USER_ERROR);
        }
        $log = $this -> Config -> get('log');
        $log_level = isset($log['level']) ? $log['level'] : $this -> all; 
        $log_path  = isset($log['path']) ? $log['path'] : APP_PATH. '/../runtime/log';
        if ($this -> levels[$level] & $log_level) {
            $file = $level."_".date('Ymd');
            $msg = sprintf($msg, ...$args);
            $msg  = sprintf("%s %s\n", date('H:i:s'), $msg);
            file_put_contents("$log_path/$file", $msg, FILE_APPEND);
        }
    }
}