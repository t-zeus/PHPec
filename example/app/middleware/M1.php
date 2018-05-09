<?php
namespace myapp\middleware;

//演示中间件编写，需 implements \PHPec\interfaces\Middleware，该接口需实现 enter($ctx) 和 leave($ctx)方法
class M1 implements \PHPec\interfaces\Middleware
{
    use \PHPec\DITrait; //拥有自动依赖注入功能

    public function enter($ctx)
    {
        $this -> Logger -> debug('M1 begin');
    }
    public function leave($ctx)
    {
        $this -> Logger -> debug('M1 end');
    }
}