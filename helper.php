<?php
//辅助函数,定义一些通用处理
namespace PHPec;

defined('APP_PATH')  || define('APP_PATH',dirname($_SERVER['SCRIPT_FILENAME']));

//Log类型
const L_TYPE = [
    'debug' => 1<<0,
    'info'  => 1<<1,
    'warn'  => 1<<2,
    'error' => 1<<3,
];
defined('LOG_PATH')  || define('LOG_PATH',APP_PATH.'/logs');
defined('LOG_LEVEL') || define('LOG_LEVEL', 15);

//路由类型
const R_TYPE = [
    'query_string' => 1,
    'path_info'    => 2,
    'RESTful'      => 3
];

if(defined('ROUTER_TYPE')){
    if(false === array_search(ROUTER_TYPE,R_TYPE)){
        trigger_error("ROUTER_TYPE error",E_USER_ERROR);
    }
}else{
    define('ROUTER_TYPE',1);    
}

//路由用到的变量，先取出来，防止有中间件更改
define('REQUEST_METHOD', $_SERVER['REQUEST_METHOD']);
define('PATH_INFO',      $_SERVER['PATH_INFO']);
define('QUERY_STRING',   $_SERVER['QUERY_STRING']);

//hander E_USER_ERROR
set_error_handler(function($errno, $errstr, $errfile, $errline){
    if($errno == E_USER_ERROR){
        throw new \Exception($errstr, 1);
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
			$this -> _before();
		}
	}
	function __destruct(){
		if(method_exists($this, '_after')){
			$this -> _after();
		}
	}
}

/**
 * Log处理对象，接受一个PHPec\LogWriter作为参数
 */
class Logger {
    function __construct(LogWriter $LogWriter = NULL){
        if(!$LogWriter) $LogWriter = new FileWriter();
        $this -> writer = $LogWriter;
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
    function write($msg,$type){
        if(LOG_LEVEL & L_TYPE[$type]){
            $fname = sprintf("%s/%s_%s",LOG_PATH,$type,date('Ymd'));
            file_put_contents($fname, sprintf("%s : %s\n",date('Y-m-d H:i:s'),$msg),FILE_APPEND);  
        }
    }
}
?>