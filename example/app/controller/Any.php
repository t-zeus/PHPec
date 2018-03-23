<?php
namespace myapp\controller;

//默认控制器
class Any {
    use \PHPec\DITrait;
    function _any($ctx){
        //如果不设置body，中间件middleware/M1.php会使用text来设置body，你可以注释掉看看
        //Config为自动注入的配置读取对象，
        //将读取config/app.php中的greet，如果没有,HelloPHPec为缺省值
        $ctx -> body = $this -> Config -> get('greet','Hello');
    }
}
