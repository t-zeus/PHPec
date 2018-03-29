<?php
namespace myapp\controller;

//默认控制器
class Any {

    use \PHPec\DITrait;

    function _any($ctx)
    {
        //Config为自动注入的配置读取对象，
        //将读取config/app.php中的greet，如果没有,Hello为缺省值
        $ctx -> body = $this -> Config -> get('greet','Hello');
    }
}
