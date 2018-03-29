<?php
//启动入口(composer autoload)

defined('APP_PATH')  || exit('APP_PATH not defined');
defined('APP_NS')  || define('APP_NS', '');

//hander E_USER_ERROR
set_error_handler(function($errno, $errstr, $errfile, $errline){
    if ($errno == E_USER_ERROR) {
        throw new \Exception($errstr, 1);
    } elseif ($errno == E_USER_WARNING) {
        echo "PHPec Warning: ".$errstr."\n\n";
        //todo: log
    }
    return false;
});
