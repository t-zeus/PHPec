<?php
namespace myapp\service;


//演示如何编写服务, Logger因为框架内也需要使用，所以需实现\PHPec\interfaces\Logger接口，以保持一致
class Loggers implements \PHPec\interfaces\Logger
{
    //添加\PHPec\DITrait,可使用自动依赖注入
    use \PHPec\DITrait;
    use \PHPec\LoggerTrait;
     
    public function log($level, $msg, ...$args)
    {
        //具体业务处理，比如按不同的level将log发送到不同的目标
    }
}