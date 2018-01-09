<?php
/**
 * @desc: PHPec框架核心类,负责中间件加载和调度
 * @github: https://github.com/tim1020/PHPec
 * @Author: Tim<tim8670@gmail.com>
 */
require __DIR__.'/interface.php';
require __DIR__.'/helper.php';

final class PHPec{
	private $middleware = array();
	private $mGenerator;
	private $ctx = [
		'status' => 200,
		'body'   => NULL
	];
	function __construct(\PHPec\LogWriter $writer = NULL){
		$this -> logger = new PHPec\Logger($writer);
		$this -> _add('\PHPec\ReqIo');
	}
	//添加中间件
	private function _add($middleware = NULL){
		if(!empty($this -> middleware )){
			$last = $this -> middleware[count($this -> middleware)-1];
			if($last === false){
				$this -> logger -> warn("middleware {$middleware} ingored");
				return;
			}
		}
		if(!$middleware) {
			$this -> middleware[] = false;
		}else{
			if($middleware instanceof Closure){
				$this -> middleware[] = $middleware;
			} else {
				$middleware = $this -> _loadMidFile($middleware);
				if(function_exists($middleware)){
					$this -> middleware[] = $middleware;
				}elseif(class_exists($middleware)){
					$m = new $middleware();
					if (!($m instanceof \PHPec\Middleware)){
						trigger_error("middleware {$middleware} invalid",E_USER_ERROR);
					}
					$this -> middleware[]= $m;
				}else{
					trigger_error("middleware class or function not found",E_USER_ERROR);
				}
			}
		}
	}
	//开始运行
	function run(){
		$this -> _add('\PHPec\Router');
		$this -> mGenerator = $this -> _generator();
		$this -> next();
	}
	//执行下一个中间件
	function next(){
		$m = $this -> mGenerator -> current();
		if(!$m) return;
		$this -> mGenerator -> next();
		if($m instanceof \PHPec\Middleware){
			$m -> begin($this);
			$this -> next();
			$m -> end($this);
		}else{
			$m($this);
		}
	}
	//返回下一个中间件对象
	private function _generator(){
		foreach($this -> middleware as $m){
			yield $m;
		}
	}
	//Overload use方法
	function __call($method,$value){
		if($method == 'use'){
			$m = isset($value[0]) ? $value[0] : false;
			$this -> _add($m);
		}else{
			trigger_error("call not defined method: PHPec -> {$method}",E_USER_ERROR);
		}
	}
	//设置$ctx的值
	function __set($k,$v){
		$this -> ctx[$k]= $v;
	}
	//读取$ctx的值
	function __get($k){
		return isset($this -> ctx[$k]) ? $this -> ctx[$k] : NULL;
	}
	function __toString(){
		return "[PHPec Appp]";
	}

	//加载中间件文件
	private function _loadMidFile($middleware){
		$classFile = $middleware;
		if(strpos($middleware, '\\PHPec\\') === 0){ //内置中间件
			$classFile = substr($middleware,7);
			$path = __DIR__.'/middleware/';
		}else{
			$path = APP_PATH.'/middleware/';
		 	if(defined('NS_MIDDLE') && NS_MIDDLE){
				$middleware = NS_MIDDLE."\\".$middleware;
		 	}
		}
		$mFile = $path.strtolower(preg_replace( '/([a-z0-9])([A-Z])/', "$1_$2",  $classFile)).".php";
		(file_exists($mFile) && require $mFile) || trigger_error("load middleware file fail -- {$mFile}",E_USER_ERROR);
		return $middleware;
	}
}
?>