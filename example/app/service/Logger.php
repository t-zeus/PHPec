<?php
namespace myapp\service;


//演示如何编写服务, 需 implements \Psr\Log\LoggerInterface
class Logger implements \Psr\Log\LoggerInterface
{
    //添加\PHPec\DITrait,可使用自动依赖注入
    use \PHPec\DITrait;
    use \Psr\Log\LoggerTrait;
     
    public function log($level, $message, array $context = array())
    {
        //具体业务处理，比如按不同的level将log发送到不同的目标
    }
}