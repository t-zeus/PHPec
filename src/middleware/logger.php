<?php
namespace PHPec;

defined('LOG_PATH')  || define('LOG_PATH',APP_PATH.'/logs');
defined('LOG_LEVEL') || define('LOG_LEVEL', 15);

class Logger implements Middleware{
    function __construct(LogWriter $LogWriter = NULL){
        if(!$LogWriter) $LogWriter = new FileWriter();
        $this -> writer = $LogWriter;
    }

    function begin($ctx){
        $ctx -> logger = $this;
    }
    function end($ctx){
    }

    function debug($msg){
        $this -> _write($msg, 'debug');
    }
    function info($msg){
        $this -> _write($msg, 'info');
    }
    function error($msg){
        $this -> _write($msg, 'error');
    }
    function warn($msg){
        $this -> _write($msg, 'warn');
    }
    private function _write($msg,$type){
        $this -> writer -> write($msg,$type);
    }
}

class FileWriter implements LogWriter{
    private $lType = [
        'debug' => 1<<0,
        'info'  => 1<<1,
        'warn'  => 1<<2,
        'error' => 1<<3,
    ];
    function write($msg,$type){
        if(LOG_LEVEL & $this -> lType[$type]){
            $fname = sprintf("%s/%s_%s",LOG_PATH,$type,date('Ymd'));
            file_put_contents($fname, sprintf("%s : %s\n",date('Y-m-d H:i:s'),$msg),FILE_APPEND);  
        }
    }
}