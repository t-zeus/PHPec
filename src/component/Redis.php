<?php
namespace PHPec\component;

class Redis {

    use \PHPec\DITrait;
    private $mConn = null;
    private $sConn = null;

    public function __construct()
    {
    }
    private function conn($type)
    {
        if ($type == 'M') {
            if (!$this -> mConn) $this -> mConn = $this -> Connection -> getRedis();
        } elseif ($type == 'S') {
            if (!$this -> sConn) $this -> sConn = $this -> Connection -> getRedis('M');
        }
    }
    //代理原Redis操作
    public function __call($method, $args){
        $slave = [
            'get','mGet', 'getMultiple','strLen','getRange',
            'hExists','hGet','hGetAll','hKeys','hLen','hMGet','hVals'
        ]; //从 的操作
        $conn = null;
        if (in_array($method, $slave)) {
            $this -> conn('S');
            $conn = $this -> sConn;
        } else {
            $this -> conn('M');
            $conn = $this -> mConn;
        }
        return call_user_func(array($conn, $method), ...$args);
    }
}