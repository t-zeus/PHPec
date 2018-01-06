<?php
//PHPec框架核心类,负责中间件加载和调度
include __DIR__.'/interface.php';
include __DIR__.'/helper.php';
include __DIR__.'/base_control.php';
defined('APP_PATH')  || define('APP_PATH',dirname($_SERVER['SCRIPT_FILENAME']));

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
			$m = new $middleware();
			if (!($m instanceof PHPec\Middleware)){
				$this -> ctx -> logger -> error("middleware {$middleware} invalid ");
				throw new \Exception("middleware {$middleware} invalid");
			}
			$this -> mObj[]= $m;
		}
		$this -> lastIdx ++;
	}
	//开始运行
	function run(){
		$this -> mObj[] = new PHPec\Router();
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
}
?>