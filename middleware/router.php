<?php
//路由中间件
namespace PHPec;

const R_TYPE = array(
	'query_string' => 1,
	'path_info'	   => 2,
	'RESTful'	   => 3
);

if(defined('ROUTER_TYPE')){
	//todo: ROUTER_TYPE 不合法
}else{
	define('ROUTER_TYPE',1);	
}

class Router implements Middleware {
	function begin($ctx){
        $reqMethod = $_SERVER['REQUEST_METHOD'];
        $pathInfo  = $_SERVER['PATH_INFO'];
        $qStr      = $_SERVER['QUERY_STRING'];
        $ctx -> logger -> debug(sprintf("reqMethod=%s,path=%s,qStr=%s",$reqMethod,$pathInfo,$qStr));
        if(ROUTER_TYPE == R_TYPE['query_string']){ 
        	parse_str($qStr, $qs);
        	$resource = isset($qs['c']) ? ucfirst(strtolower($qs['c'])) : '';
        	$action   = isset($qs['a']) ? strtolower($qs['a']) : '_any';
        }else{
        	$path = explode("/",$pathInfo);
        	array_shift($path);
        	$resource = isset($path[0]) ? ucfirst(strtolower($path[0])) : '';
        	$action   = isset($path[1]) ? strtolower($path[1]) : '_any';
        	if(ROUTER_TYPE == R_TYPE['RESTful']){
        		$action = strtolower($reqMethod);
        		$ctx -> resId = array();
        		if($resource && isset($path[1])) $ctx->resId[strtolower($path[0])] = $path[1];
        		if(isset($path[2])) {
        			$resource.= ucfirst(strtolower($path[2]));
        		}
        		if(isset($path[3])){
        			$ctx -> resId[strtolower($path[2])] = $path[3];
        		}
        	}
        }
        $ctx -> logger -> debug(sprintf('router result, type=%s, target=%s->%s',ROUTER_TYPE, $resource,$actoin));
        if(!$resource){
        	return $this -> _notfound('Resource not found',$ctx);
        }
        //转回文件名格式
        $resFile = APP_PATH.'/controller/'.strtolower(preg_replace( '/([a-z0-9])([A-Z])/', "$1_$2", $resource)).".php";
        if(file_exists($resFile)) include $resFile;
        else{
        	$resFile = APP_PATH.'/controller/any.php';
        	if(file_exists($resFile)){
        		include $resFile;
        		$resource = "Any";
        	}
        }
        if(!class_exists($resource)){
        	return $this-> _notfound('Resource class not found --'.$resource,$ctx);
        }
        $res = new $resource($ctx);
        if( method_exists($res, $action)){
        	$res->$action();
        }else if( method_exists($res, '_any')){
        	$res->_any();
        }else{
        	return $this-> _notfound('action not found',$ctx);
       	}
	}

	function _notfound($msg,&$ctx){
		$ctx -> logger -> info($msg);
		//todo: 定制返回
		echo $msg;
	}
	function end($ctx){
		//do nothing
	}
}
?>