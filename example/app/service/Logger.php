<?php
namespace myapp\service;


//custom Logger, must implements \Psr\Log\LoggerInterface
class Logger implements \Psr\Log\LoggerInterface
{
    use \PHPec\DITrait;
    use \Psr\Log\LoggerTrait;
     
    public function log($level, $message, array $context = array())
    {
        //todo: check $level
        $path = $this -> Config -> get('log_path', APP_PATH.'/../runtime/log');
        $file = $level."_".date('ymd');
        $msg  = sprintf("%s %s\n", date('H:i:s'), $message);
        file_put_contents("$path/$file", $msg, FILE_APPEND);
    }
}