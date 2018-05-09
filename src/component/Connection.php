<?php
namespace PHPec\component;

//获取指定服务的链接
class Connection
{
    use \PHPec\DITrait;

    private $conns = [];
    private $handle = [];
    
    //$method = getXxxxM,getXxxS,getXxx('M'),getXxx('S'),getXxx();
    public function __call($method, $args) {
        if (0 === strpos($method, 'get')) {
            $sType = substr($method, -1);
            if ($sType == 'M' || $sType == 'S') {  //getXxxM,getXxxS
                $sName = substr($method, 3, -1);
            } else { //getXxx
                $sName = substr($method, 3);
                $sType = empty($args[0]) ? 'M' : $args[0];
                if ($sType != 'M' && $sType != 'S') {
                    trigger_error("$method params error: 'M' or 'S' expected", E_USER_ERROR);
                }
            }
           
            $key    = sprintf("%s_%s", $sType, $sName);
            if (empty($this -> conns[$key])) {
                $class = "\\PHPec\\connections\\$sName";
                if (!class_exists($class)) trigger_error("Connection class not found: ".$class, E_USER_ERROR);
                $this -> conns[$key] = call_user_func(array(new $class, 'getConn'), $sType);
            }
            return $this -> conns[$key];
        }
    }
}
