<?php
//指定项目文件根路径
define('APP_PATH',__DIR__.'/../app');
//指定项目根命名空间
define('APP_NS', 'myapp');
//composer autoload
require __DIR__.'/../vendor/autoload.php';

try {
    $app = new \PHPec\App();
    //框架内置ViewRender中间件
    $app -> use('PHPec\middleware\ViewRender');
    //自定义中间件
    // $app -> use(function($ctx){
    //     $ctx -> body = 'hello';
    //     $ctx -> next();
    //     $ctx -> body = 'world';
    // });
    $app -> use ('M1'); //自定义中间件，传入时可省略命名空间
    //$app -> use(['M1','myapp\middleware\M2']); //用数组传入多个中间件
    //$app -> use(); //传递空参数时，所有后面的中间件被忽略，包括内置自动加载的Router;
    //$app -> use('M3','param1'); //可以接受第二个参数作为其构造函数的参数
    //如果没之前没有传入空参数作为声明，框架自动调用内置的Router中间件进行路由
    $app->run();

} catch (Exception $e) {
    var_dump($e);
}