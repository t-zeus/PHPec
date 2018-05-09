<?php
namespace myapp\controller;

/**
 * 继承\PHPec\BaseControl,  _before($ctx)和_after($ctx)会被自动调用，如果不需要此特性，也可以不继承
 * 如果多个Controller都需要执行同一个_before或_after
 * 可以自行再实现一个Base类去继承\PHPec\BaseControl，然后其它Controller再继承它。
 */
class Task extends \PHPec\BaseControl
{   
    //使用自动依赖注入特性
    use \PHPec\DITrait;
    
    public function add($ctx)
    {
        
    }    

    //default action
    function _any($ctx){
        //TaskModel是框架自动生成的数据操作对象，对应task表，拥有基本的crud操作，并支持事务
        $ctx -> body = $this -> TaskModel -> get('1=1');
        //print_r($ctx -> body);
        //使用$ctx->template来指定模板，相对app/view目录，使用.tpl后缀
        $ctx -> template = 'task';
        //也可使用return来指定模板名称,如果不显式指定,使用$ctx->resource/$ctx->action,即控制器名加动作名，
        //return 'task';
    }


    function _before($ctx)
    {
        //$this -> halt = true; 
        //$ctx -> body ='[before]';
    }
}
