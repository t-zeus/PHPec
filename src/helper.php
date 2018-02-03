<?php
//辅助函数,定义一些通用处理
namespace PHPec;

defined('APP_PATH')  || define('APP_PATH',dirname($_SERVER['SCRIPT_FILENAME']));

//hander E_USER_ERROR
set_error_handler(function($errno, $errstr, $errfile, $errline){
    if($errno == E_USER_ERROR){
        throw new \Exception($errstr, 1);
    }elseif($errno == E_USER_WARNING){
        echo "PHPec Warning: ".$errstr."\n\n";
        //todo: log
    }
    return false;
});

/**
 * 控制器基类
 */
class BaseControl {
    function __construct($ctx){
        $this->ctx = $ctx;
        if(method_exists($this, '_before')){
            $this -> _before($this->ctx);
        }
    }
    function __destruct(){
        if(method_exists($this, '_after')){
            $this -> _after($this->ctx);
        }
    }
}
?>