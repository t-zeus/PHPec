<?php
//路由中间件
namespace PHPec;

class Router implements Middleware {
	function begin($ctx){ //$ctx->router= array(type=>,method=>,pathinfo=>,query=>)
        $r = $ctx -> router;
        if(empty($r) || empty($r['type']) || empty($r['method'])){
            trigger_error("router param miss",E_USER_ERROR);
        }
        //todo: check type,method
        $ctx -> logger -> debug(sprintf("reqMethod=%s,path=%s,qStr=%s",$r['method'],$r['pathinfo'],$r['query']));
        if($r['type'] == R_TYPE['query_string']){
        	parse_str($r['query'], $qs);
        	$resource = isset($qs['c']) ? $qs['c'] : 'Any';
        	$action   = isset($qs['a']) ? $qs['a'] : '_any';
        }else{
            if($r['pathinfo']==null) trigger_error("PATH_INFO invalid",E_USER_ERROR);
        	$path = explode("/",$r['pathinfo']);
        	array_shift($path);
        	$resource = isset($path[0]) ? $path[0] : 'Any';
        	$action   = isset($path[1]) ? $path[1] : '_any';
        	if($r['type'] == R_TYPE['RESTful']){
        		$action = strtolower($r['method']);
        		$ctx -> resId = array();
        		if($resource && isset($path[1])) $ctx->resId[$path[0]] = $path[1];
        		if(isset($path[2])) {
        			$resource.= $path[2];
        		}
        		if(isset($path[3])){
        			$ctx -> resId[$path[2]] = $path[3];
        		}
        	}
        }
        $ctx -> logger -> debug(sprintf('router result, type=%s, target=%s->%s',$r['type'], $resource,$action));
        if(!$resource){
        	return $this -> _notFound('Resource not found',$ctx);
        }
        //转回文件名格式
        $resFile = APP_PATH.'/controller/'.strtolower(preg_replace( '/([a-z0-9])([A-Z])/', "$1_$2", $resource)).".php";

        if(file_exists($resFile)) include_once $resFile;
        else{
        	$resFile = APP_PATH.'/controller/any.php';
        	if(file_exists($resFile)){
        		include_once $resFile;
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