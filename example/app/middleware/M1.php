<?php
namespace example\middleware;

class M1 implements \PHPec\interfaces\Middleware
{
    use \PHPec\DITrait;
    public function begin($ctx)
    {
        $this -> Logger -> debug('M1 begin');
    }
    public function end($ctx)
    {
        $this -> Logger -> debug('M1 end');
    }
}