<?php
//辅助函数,定义一些通用处理
namespace PHPec;

const L_TYPE = array(
    'debug' => 1<<0,
    'info'  => 1<<1,
    'warn'  => 1<<2,
    'error' => 1<<3,
);

defined('APP_PATH')  || define('APP_PATH',dirname($_SERVER['SCRIPT_NAME']));
defined('LOG_PATH')  || define('LOG_PATH',APP_PATH.'/logs');
defined('LOG_LEVEL') || define('LOG_LEVEL', L_ERROR);


/**
 * 类自动加载(类名格式为CamelCase,文件名格式为snake_case,即UserAdd => user_add.php)
 */
spl_autoload_register(function ($class) {
    if(strpos($class, 'PHPec\\') === 0){ //内置中间件
        $classFile = substr($class,6);
        $searchPath = array(__DIR__.'/middleware/' );
    }else{
        $searchPath = array(
        	APP_PATH.'/middleware/',
        	APP_PATH.'/controller',
        );
    }
    $file = strtolower(preg_replace( '/([a-z0-9])([A-Z])/', "$1_$2", $classFile ? $classFile : $class )).".php";
    foreach($searchPath as $d){
    	$fullPath = $d.$file;
    	if(file_exists($fullPath)){
    		include $fullPath;
            if(!class_exists($class)) throw new \Exception("Class {$class} Not found");
    	}
    }
    //todo: 找不到文件
});


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