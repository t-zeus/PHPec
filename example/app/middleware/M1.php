<?php
namespace myapp\middleware;

//演示中间件编写，需 implements \PHPec\interfaces\Middleware，该接口需实现 begin($ctx) 和 end($ctx)方法
class M1 implements \PHPec\interfaces\Middleware
{
    use \PHPec\DITrait; //拥有自动依赖注入功能

    public function begin($ctx)
    {
        $this -> Logger -> debug('M1 begin');
    }
    public function end($ctx)
    {
        $this -> Logger -> debug('M1 end');
    }
}