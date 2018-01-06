<?php
//路由中间件
namespace PHPec;

class Router implements Middleware {
	function begin($ctx){
		echo "----router---\n";
	}
	function end($ctx){
	}
}
?>