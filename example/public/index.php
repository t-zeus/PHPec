<?php
define('APP_PATH',__DIR__.'/../app');
define('APP_NS', 'myapp');

require __DIR__.'/../vendor/autoload.php'; //composer autoload

try {
    $app = new \PHPec\App();
    //加载中间件
    // $app -> use(function($ctx){
    //     $ctx -> body = 'hello';
    //     $ctx -> next();
    //     $ctx -> body = 'world';
    // });
    $app -> use ('M1');
    //$app -> use('PHPec\middleware\JWT'); //PHPec内置中间件
    //$app -> use(['M1','myapp\middleware\uu\M2']); //用数组传入多个中间件
    //$app -> use(); //传递空参数时，所有后面的中间件被忽略，包括内置自动加载的Router;
    //$app -> use('M3','param1'); //可以接受第二个参数作为其构造函数的参数

    $app->run();

} catch (Exception $e) {
    var_dump($e);
}