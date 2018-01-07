<?php
//PHPec框架核心类,负责中间件加载和调度
defined('APP_PATH')  || define('APP_PATH',dirname($_SERVER['SCRIPT_FILENAME']));
include __DIR__.'/interface.php';
include __DIR__.'/helper.php';

final class PHPec{
	private $mObj = array();
	private $lastIdx = -1;
	private $mGenerator;
	private $ctx = null;
	function __construct(PHPec\FileWriter $writer = NULL){
		$this -> ctx = new stdClass; //用来保存上下文，保证各中间件可以共用
		$this -> ctx -> logger = new PHPec\Logger($writer);
	}
	//添加中间件
	function use(String $middleware = ''){
		if($this -> lastIdx >= 0){
			$last = $this -> mObj[$this -> lastIdx];
			if($last === false){
				$this -> ctx -> logger -> warn("middleware {$middleware} ingored");
				return;
			}
		}
		if(!$middleware) {
			$this -> mObj[] = false;
		}else{
			try{
				$middleware = $this -> _loadMidFile($middleware);
				$m = new $middleware();
				if (!($m instanceof PHPec\Middleware)){
					throw new Exception("middleware {$middleware} invalid");
				}
				$this -> mObj[]= $m;
			}catch(throwable $e){
				throw $e;
			}
		}
		$this -> lastIdx ++;
	}
	//开始运行,
	function run(){
		$this -> use('PHPec\Router');
		$this -> mGenerator = $this -> _generator();
		$this -> _next();
	}
	//执行下一个中间件
	private function _next(){
		$m = $this -> mGenerator -> current();
		if(!$m) return;
		$this -> mGenerator -> next();
		$m -> begin($this -> ctx);
		$this -> _next();
		$m -> end($this -> ctx);
	}
	//返回下一个中间件对象
	private function _generator(){
		foreach($this -> mObj as $m){
			yield $m;
		}
	}
	//加载中间件文件
	private function _loadMidFile($middleware){
		$classFile = $middleware;
		if(strpos($middleware, 'PHPec\\') === 0){ //内置中间件
        	$classFile = substr($middleware,6);
        	$path = __DIR__.'/middleware/';
    	}else{
        	$path = APP_PATH.'/middleware/';
        	if(defined('NS_MIDDLE') && NS_MIDDLE){
        		$middleware = NS_MIDDLE."\\".$middleware;
        	}
    	}
    	include $path.strtolower(preg_replace( '/([a-z0-9])([A-Z])/', "$1_$2",  $classFile)).".php";
    	return $middleware;
	}
}
?>