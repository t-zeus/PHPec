<?php
//辅助函数,定义一些通用处理
namespace PHPec;

const L_TYPE = array(
    'debug' => 1<<0,
    'info'  => 1<<1,
    'warn'  => 1<<2,
    'error' => 1<<3,
);

defined('LOG_PATH')  || define('LOG_PATH',APP_PATH.'/logs');
defined('LOG_LEVEL') || define('LOG_LEVEL', 15);

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