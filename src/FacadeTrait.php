<?php
namespace PHPec;

/**
 * 为Model提供静态调用的方式
 */
Trait FacadeTrait
{
    public static function __callStatic($method, $args)
    {
        $class = __CLASS__;
        $objs = Container::getInstance();
        $k = "{$class}Model";
        if (isset( $objs -> $k)) {
            $obj = $objs -> $k;
        } else {
            $table = $class;
            $class = '\\PHPec\\component\\PDO';
            $obj = new $class($table);
            $objs -> $k = $obj;
        }
        return call_user_func(array($obj, $method), ...$args);
    }
}