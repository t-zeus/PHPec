<?php
namespace PHPec;

/**
 * PHPec A easy,simple and Lightweight php web framework
 * 
 * @Author Tim <tim8670@gmail.com>
 */
final class App
{
    private $middleware = [];
    private $mGenerator;
    private $ctx = [
        'status' => 200,
        'body'   => null,
        'resHeaders' => ['X-Powered-By' => 'PHPec']
    ];
    
    public function __construct()
    {
        $this -> _add('\PHPec\middleware\CommonIO');
    }
    public function run()
    {
        $this -> _add('\PHPec\middleware\Router');
        $this -> mGenerator = $this -> _generator();
        $this -> next();
    }
    public function next()
    {
        $m = $this -> mGenerator -> current();
        if (!$m) return;
        $this -> mGenerator -> next();
        if ($m instanceof interfaces\Middleware) {
            $r = $m -> enter($this);
            if (false !== $r) $this -> next(); //do next when enter return not false;
            $m -> leave($this);
        } else {
            $m($this);
        }
    }
    //set response body and httpCode
    public function res($body, $status = 200)
    {
        $this -> body = $body;
        $this -> status = $status;
        return true;
    }
    //set responseHeader
    public function setHeader($k, $v)
    {
        $this -> ctx['resHeaders'][$k] = $v;
    }
    //Overload use method
    public function __call($method, $value)
    {
        if ($method == 'use') {
            $m = array_shift($value);
            if (is_array($m)) {   
                foreach ($m as $v) {
                    $this -> _add($v);  
                }
            } else {
                $param = empty($value) ? null : $value[0];
                $this -> _add($m, $param);
            }
        } else {
            if (in_array($method, ['get','post','cookie'])) {
                if (isset($this -> ctx[$method]) && is_callable($this -> ctx[$method]) ) {
                    return call_user_func($this -> ctx[$method], ...$value);
                }
            }
            trigger_error("PHPec -> {$method} not defined", E_USER_ERROR);
        }
    }
    //set $ctx props
    public function __set($k,$v)
    {
        if (stripos($k, '_') === 0 && isset($this -> ctx[$k])) { 
            trigger_error("\$ctx->{$k} cannot modify", E_USER_WARNING);
        } else {
            $this -> ctx[$k] = $v;
        }
    }
    //get $ctx props
    public function __get($k)
    {
        return isset($this -> ctx[$k]) ? $this -> ctx[$k] : null;
    }
    public function __isset($k)
    {
        return isset($this -> ctx[$k]);
    }
    public function __toString()
    {
        return "[PHPec App]";
    }

    //add middleware
    private function _add($middleware = null, $param = null)
    {
        if (!empty($this -> middleware )) {
            if (false === $this -> middleware[count($this -> middleware)-1]) {
                return; //skip
            }
        }
        if (!$middleware) {
            $this -> middleware[] = false;
        } else {
            if (is_string($middleware)) {
                if (false === strpos($middleware, "\\" )) {
                    $middleware = APP_NS.'\middleware\\' .$middleware;
                }
                $middleware = new $middleware($param); //autoload
            }
            if (is_object($middleware)) {
                if ($middleware instanceof \Closure || $middleware instanceof interfaces\Middleware) {
                    $this -> middleware[] = $middleware;
                } else {
                    trigger_error('middleware invalid:' .get_class($middleware) .' must implement \PHPec\interfaces\Middleware', E_USER_ERROR);
                }
            } else {
                trigger_error("middleware invalid: type error", E_USER_ERROR);
            }
        }
    }
    //yield next middleware
    private function _generator()
    {
        foreach ($this -> middleware as $m) {
            yield $m;
        }
    }
}
?>
