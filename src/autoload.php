<?php
//启动入口(非composer方式)
require __DIR__. '/bootstrap.php';

//自动加载框架及应用的class
spl_autoload_register(function($class){
    $path = explode('\\', $class);
    $ns = array_shift($path);
    if ($ns == 'PHPec') {
        $prefix = __DIR__;
    } elseif ($ns == APP_NS) {
        $prefix = APP_PATH;
    } else {
        return;
    }
    $classFile = $prefix. '/'. implode("/",$path).'.php';
    file_exists($classFile) && require $classFile;
});