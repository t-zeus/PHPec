<?php
//路由中间件
namespace PHPec;

class Router implements Middleware {
	function begin($ctx){
        $ctx -> logger -> debug(sprintf("reqMethod=%s,path=%s,qStr=%s",REQUEST_METHOD,PATH_INFO,QUERY_STRING));
        if(ROUTER_TYPE == R_TYPE['query_string']){ 
        	parse_str(QUERY_STRING, $qs);
        	$resource = isset($qs['c']) ? ucfirst(strtolower($qs['c'])) : '';
        	$action   = isset($qs['a']) ? strtolower($qs['a']) : '_any';
        }else{
        	$path = explode("/",PATH_INFO);
        	array_shift($path);
        	$resource = isset($path[0]) ? ucfirst(strtolower($path[0])) : '';
        	$action   = isset($path[1]) ? strtolower($path[1]) : '_any';
        	if(ROUTER_TYPE == R_TYPE['RESTful']){
        		$action = strtolower(REQUEST_METHOD);
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
        $ctx -> logger -> debug(sprintf('router result, type=%s, target=%s->%s',ROUTER_TYPE, $resource,$action));
        if(!$resource){
        	return $this -> _notFound('Resource not found',$ctx);
        }
        //转回文件名格式
        $resFile = APP_PATH.'/controller/'.strtolower(preg_replace( '/([a-z0-9])([A-Z])/', "$1_$2", $resource)).".php";

        if(file_exists($resFile)) include $resFile;
        else{
        	$resFile = APP_PATH.'/controller/any.php';
        	if(file_exists($resFile)){
        		include $resFile;
        		$resource = "Any";
        	}else{
                return $this -> _notFound("Resource file not found",$ctx);
            }
        }
        if(defined('NS_CONTROL') && NS_CONTROL) $resource = NS_CONTROL."\\".$resource;
        if(!class_exists($resource)){
        	return $this-> _notFound('Resource class not found --'.$resource, $ctx);
        }
        $res = new $resource($ctx);
        if( method_exists($res, $action)){
        	$res->$action($ctx);
        }else if( method_exists($res, '_any')){
        	$res->_any($ctx);
        }else{
        	return $this-> _notFound('action not found',$ctx);
       	}
	}

	function _notFound($msg,$ctx){
		$ctx -> logger -> info($msg);
		$ctx -> status = 404;
        $ctx -> body = $msg;
	}
	function end($ctx){
		//do nothing
	}
}
?>