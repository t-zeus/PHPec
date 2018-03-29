<?php
namespace PHPec;

/*
 * singleton objects container
 */
class Container
{
    private static $_instance = null;
    private $objs = [];
    //can not new
    private function __construct()
    {
    }
    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
           self::$_instance = new self;
        }
        return self::$_instance;
    }
    public function __get($k)
    {
        return isset($this -> objs[$k]) ? $this -> objs[$k] : null;
    }
    public function __isset($k)
    {
        return isset($this -> objs[$k]);
    }
    public function __set($k, $v)
    {
        $this -> objs[$k] = $v;
    }
}