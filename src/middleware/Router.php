<?php
namespace PHPec\middleware;

final class Router implements \PHPec\interfaces\Middleware
{
    use \PHPec\DITrait;
    const DEFAULT_RES    = 'Any';
    const DEFAULT_ACTION = '_any';
    const DEFAULT_TYPE   = 'PATHINFO';
    const ALL_TYPES      = 'PATHINFO, QUERY_STRING, RESTFUL';
    const ALL_METHODS    = 'GET, POST, PUT, DELETE, OPTIONS';
    
    public function enter($ctx)
    {   
        $type    = $this -> Config -> get('route_type', self::DEFAULT_TYPE);
        $methods = $this -> Config -> get('allowed_method', self::ALL_METHODS);
        if (false === strpos(self::ALL_TYPES, $type)) {
            $expect = self::ALL_TYPES;
            return $ctx -> res("invalid R_TYPE {$type}, expect [{$expect}]", 500);
        }
        if (!$ctx -> method || false === stripos($methods, $ctx -> method)) {
            return $ctx -> res('Method Not Allowed', 405);
        }
        if ($type == 'QUERY_STRING') { //c=Controller&a=Action
            parse_str($ctx -> query_str, $qs);
            $resource = !empty($qs['c']) ? $qs['c'] : self::DEFAULT_RES;
            $action   = !empty($qs['a']) ? $qs['a'] : self::DEFAULT_ACTION;
        } else { //pathinfo or RESTful
            if (!$ctx -> pathinfo) {
                return $ctx -> res("PATHINFO invalid", 500);
            }
            $path = explode("/", $ctx -> pathinfo);
            array_shift($path);
            if (empty($path[0])) {
                $resource = self::DEFAULT_RES;
                $action   = self::DEFAULT_ACTION;
            } else {
                if ($type == 'PATHINFO') { // /A/B/C => res=/A/B,action=C
                    $action = array_pop($path);
                    if (empty($path)) {
                        $resource = $action;
                        $action = self::DEFAULT_ACTION;
                    } else {
                        $resource = implode("/" , $path);
                    }
                } elseif ($type == 'RESTFUL') { // /Shop/1/Prod/2,action=req method
                    $action = strtolower($ctx -> method);
                    $resId = [];
                    for ($i = 0; $i < count($path); $i++) {
                        $k = $path[$i];
                        $v = isset($path[$i+1]) ? $path[++$i] : '';
                        $resId[$k] = $v;
                    }
                    $ctx -> resId = $resId;
                    $resource = implode("/" , array_keys($resId));
                }
            }
        }
        $ctx -> template = strtolower($resource.'/'.$action);
        //for autoload
        $resource = APP_NS.'\\controller\\'.str_replace('/','\\',$resource);
        $res = new $resource($ctx);
        if (!empty($res -> halt)) return;
        if (!method_exists($res, $action)) $action = self::DEFAULT_ACTION;
        if ( method_exists($res, $action)) {
            $tpl = $res -> $action($ctx);
            if (is_string($tpl)) {
                $ctx -> template = $tpl;
            }
        } else {
            return $ctx -> res('action not found', 404);
       	}
    }

    function leave($ctx)
    {
        //do nothing
    }
}
?>
