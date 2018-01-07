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
	function __construct(\PHPec\LogWriter $writer = NULL){
		$this -> ctx = new stdClass; //用来保存上下文，保证各中间件可以共用
		$this -> ctx -> logger = new PHPec\Logger($writer);
		$this -> ctx -> headers = array();
	}
	function __call($method,$value){
		if($method == 'use'){
			$m = isset($value[0]) ? $value[0] : false;
			$this -> add($m);
		}
	}
	//添加中间件
	function add($middleware = NULL){
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
				if (!($m instanceof \PHPec\Middleware)){
					throw new Exception("middleware {$middleware} invalid");
				}
				$this -> mObj[]= $m;
			}catch(throwable $e){
				throw $e;
			}
		}
		$this -> lastIdx ++;
	}
	// //开始运行,
	function run(){
		$this -> use('\PHPec\Router');
		$this -> mGenerator = $this -> _generator();
		$this -> _next();
		//after all middleware,render the response
		$this -> _render();
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
		if(strpos($middleware, '\\PHPec\\') === 0){ //内置中间件
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
	//response
	private function _render(){
		header("X-Powered-By: PHPec @php-".PHP_VERSION);
		$code = array (
			100 => "HTTP/1.1 100 Continue",
			101 => "HTTP/1.1 101 Switching Protocols",
			200 => "HTTP/1.1 200 OK",
			201 => "HTTP/1.1 201 Created",
			202 => "HTTP/1.1 202 Accepted",
			203 => "HTTP/1.1 203 Non-Authoritative Information",
			204 => "HTTP/1.1 204 No Content",
			205 => "HTTP/1.1 205 Reset Content",
			206 => "HTTP/1.1 206 Partial Content",
			300 => "HTTP/1.1 300 Multiple Choices",
			301 => "HTTP/1.1 301 Moved Permanently",
			302 => "HTTP/1.1 302 Found",
			303 => "HTTP/1.1 303 See Other",
			304 => "HTTP/1.1 304 Not Modified",
			305 => "HTTP/1.1 305 Use Proxy",
			307 => "HTTP/1.1 307 Temporary Redirect",
			400 => "HTTP/1.1 400 Bad Request",
			401 => "HTTP/1.1 401 Unauthorized",
			402 => "HTTP/1.1 402 Payment Required",
			403 => "HTTP/1.1 403 Forbidden",
			404 => "HTTP/1.1 404 Not Found",
			405 => "HTTP/1.1 405 Method Not Allowed",
			406 => "HTTP/1.1 406 Not Acceptable",
			407 => "HTTP/1.1 407 Proxy Authentication Required",
			408 => "HTTP/1.1 408 Request Time-out",
			409 => "HTTP/1.1 409 Conflict",
			410 => "HTTP/1.1 410 Gone",
			411 => "HTTP/1.1 411 Length Required",
			412 => "HTTP/1.1 412 Precondition Failed",
			413 => "HTTP/1.1 413 Request Entity Too Large",
			414 => "HTTP/1.1 414 Request-URI Too Large",
			415 => "HTTP/1.1 415 Unsupported Media Type",
			416 => "HTTP/1.1 416 Requested range not satisfiable",
			417 => "HTTP/1.1 417 Expectation Failed",
			500 => "HTTP/1.1 500 Internal Server Error",
			501 => "HTTP/1.1 501 Not Implemented",
			502 => "HTTP/1.1 502 Bad Gateway",
			503 => "HTTP/1.1 503 Service Unavailable",
			504 => "HTTP/1.1 504 Gateway Time-out",
			505 => "HTTP Version not supported"
		);
		if(!isset($this -> ctx -> status)) $this -> ctx -> status = 200;

		if(!isset($code[$this -> ctx -> status])){
			echo "Unknown http status code";
		}else{
			http_response_code($this -> ctx -> status);
			$contentType = 'text/html;charset=utf-8';
			if(!empty($this -> ctx -> body)){
				if(is_array($this -> ctx -> body) ||is_object($this -> ctx -> body)){
					$contentType = "text/html;charset=utf-8";
			 		$this -> ctx -> body = json_encode($this -> ctx -> body);
				}
	 		}else{
				$this -> ctx -> body =  $code[$this -> ctx -> status];
			}
			header("Content-Type: {$contentType}");
			if(!empty($this -> ctx -> headers)){
				foreach($this -> ctx -> headers as $header){
					header($header);
				}
			}
			echo $this -> ctx -> body;
		}
	}
}
?>