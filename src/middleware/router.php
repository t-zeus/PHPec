<?php
//路由中间件
namespace PHPec;

class Router implements Middleware {
    function begin($ctx){ //$ctx->route_param= array(type=>,method=>,pathinfo=>,query=>)
        $r = $ctx -> route_param;
        if(empty($r) || empty($r['type']) || empty($r['method'])){
            trigger_error("router param miss",E_USER_ERROR);
        }
        $r['method'] = strtolower($r['method']);
        if(!in_array($r['method'],$ctx -> allowedMethod)){
		return $ctx -> res('Method not allowed',405);
	}
        $ctx -> logger -> debug(sprintf("reqMethod=%s,path=%s,qStr=%s",$r['method'],$r['pathinfo'],$r['query']));
        if($r['type'] == R_TYPE['query_string']){
            parse_str($r['query'], $qs);
            $resource = !empty($qs['c']) ? $qs['c'] : 'Any';
            $action   = !empty($qs['a']) ? $qs['a'] : '_any';
        }else{
            if($r['pathinfo']==null) trigger_error("PATH_INFO invalid",E_USER_ERROR);
            $path = explode("/",$r['pathinfo']);
            array_shift($path);
            $resource = !empty($path[0]) ? $path[0] : 'Any';
            $action   = !empty($path[1]) ? $path[1] : '_any';
            if($r['type'] == R_TYPE['RESTful']){
                $action = $r['method'];
                if($resource !='Any'){ //do noting if Any
                    $resId = [];
                    if(!empty($path[1])) $resId[$path[0]] = $path[1];
                    if(!empty($path[2])) {
                        $resource.= $path[2];
                    }
                    if(!empty($path[3])){
                        $resId[$path[2]] = $path[3];
                    }
                    $ctx -> resId = $resId;
                }
            }
        }
        $ctx -> logger -> debug(sprintf('router result, type=%s, target=%s->%s',$r['type'], $resource,$action));
        //安全限制
        if(preg_match('/^[A-Z]{1}[A-Za-z\d]*$/',$resource) === 0){
            return $ctx -> res('Resource name invalid',404);
        }
        if($action!='_any' && preg_match('/^[a-z]{1}[A-Za-z\d]*$/',$action) === 0){
            return $ctx -> res('action name invalid',404);
        }
        //注入resource及action，在路由失败时由其它路由组件补充
        $ctx -> resource = $resource;
        $ctx -> action   = $action;

        //转回文件名格式
        $resFile = APP_PATH.'/controller/'.strtolower(preg_replace( '/([a-z0-9])([A-Z])/', "$1_$2", $resource)).".php";

        if(file_exists($resFile)) include_once $resFile;
        else{
            $resFile = APP_PATH.'/controller/any.php';
            if(file_exists($resFile)){
                include_once $resFile;
                $resource = "Any";
            }else{
                return $ctx -> res("Resource file not found",404);
            }
        }
        if(defined('NS_CONTROL') && NS_CONTROL) $resource = NS_CONTROL."\\".$resource;
        if(!class_exists($resource)){
            return $ctx -> res('Resource class not found --'.$resource, 404);
        }
        $res = new $resource($ctx);
        if( method_exists($res, $action)){
            $res->$action($ctx);
        }else if( method_exists($res, '_any')){
            $res->_any($ctx);
        }else{
            return $ctx -> res('action not found',404);
       	}
    }

    function end($ctx){
        //do nothing
    }
}
?>
