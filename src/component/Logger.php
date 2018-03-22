<?php
namespace PHPec\component;

class Logger implements \Psr\Log\LoggerInterface
{
    use \PHPec\DITrait;
    use \Psr\Log\LoggerTrait;

    private $levels = [
        'debug'     => 1,
        'info'      => 2,
        'notice'    => 4,
        'warning'   => 8,
        'error'     => 16,
        'critical'  => 32,
        'alert'     => 64,
        'emergency' => 128
    ];

    //private $all = 1 << 8 - 1;
     
    public function log($level, $message, array $context = array())
    {
        if (!isset($this -> levels[$level])) { //throw exception
            throw new \Psr\Log\InvalidArgumentException();
        } 
        $log_level = $this -> Config -> get('log_level', 1 << 8 -1);
        if ($this -> levels[$level] & $log_level) {
            $path = $this -> Config -> get('log_path', APP_PATH.'/../runtime/log');
            $file = $level."_".date('ymd');
            $msg  = sprintf("%s %s\n", date('H:i:s'), $message);
            file_put_contents("$path/$file", $msg, FILE_APPEND);
        }
    }
}