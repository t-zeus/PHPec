<?php
namespace PHPec\middleware;

//基于资源的路由，自带根据请求方法进行简单CRUD的操作，仅当需要时创建控制器
//res=表名,使用大驼峰（res=UserName对应到user_name表）,如果只有一层，则大小写均可
final class ResRouter implements \PHPec\interfaces\Middleware
{
    use \PHPec\DITrait;
    
    //请求方法与pdo方法的对应
    private $methodsMap  = ['GET' => 'get', 'POST' => 'add', 'PUT' => 'update', 'DELETE' => 'delete']; 
    
    //{code:xxx,error:xxx|data:xxx}
    public function enter($ctx)
    {   
        if (!$ctx -> method || !isset($this -> methodsMap[$ctx -> method])) {
            return $ctx -> res('Method Not Allowed', 405);
        }
        $resource = $ctx -> get('res',null, function($val){return ucfirst($val);});
        if (empty($resource) || !preg_match('/^[a-z]+$/i', $resource)) {
            return $ctx -> res(["code"=>-1,"error"=>'没有设置res参数或res参数非法']);
        }

        $action = strtolower($ctx -> method);

        //如果有定义对应的controller和action，执行该指定action
        $file = APP_PATH."/controller/{$resource}.php";
        $class = APP_NS.'\\controller\\'.$resource;
        if (file_exists($file)) {
            require_once $file;
            if (class_exists($class)) {
                $obj = new $class($ctx);
                if (method_exists($obj, $action)){
                    $obj -> $action($ctx);
                    return;
                }
            }
        }

        //未定义指定控制器，自动调用Model相应的CRUD方法处理
        $model = $resource."Model";
        if (!$this -> {$model} -> isExists()) {
            return $ctx -> res(["code"=>-1,"error"=>'资源表不存在']);
        }
        $method = $this -> methodsMap[$ctx -> method];
        switch ($method) {
            case 'add':
                if (empty($ctx -> _P)) {
                    return $ctx -> res(['code'=>-1, 'error' => '未设置数据字段内容']);
                }
                $params = [$ctx -> _P];
                break;
            case 'delete':
                $id = $ctx -> get('id');
                if (empty($id)) {
                    return $ctx -> res(['code'=>-1, 'error' => '缺少id参数']);
                }
                $params = [["id=?", $id]]; //根椐id删除
                break;
            case 'update':
                $id = $ctx -> get('id');
                if (empty($id)) {
                    return $ctx -> res(['code'=>-1, 'error' => '缺少id参数']);
                }
                //var_dump($ctx);
                if (empty($ctx -> _P)) {
                    return $ctx -> res(['code'=>-1, 'error' => '未设置要更新的字段内容']);
                }
                $params = [["id=?", $id], $ctx -> _P]; //[where,data];
                break;
            case 'get': //有指定id，获取指定记录,未指定时，按get参数条件
                $options['fields'] = $ctx -> get('fields','*');
                if ($id = $ctx -> get('id')) {
                    $method = 'getOne';
                    $where = ["id=?", $id];
                } else {
                    if (!empty($ctx -> get('sort'))) $options['sort'] = $ctx -> get('sort');
                    $options['page']     = $ctx -> get('page', 1);
                    $options['pageSize'] = $ctx -> get('page_size', 10);
                    $where = $ctx -> get('where','1=1');
                }
                $params = [$where, $options];
                break;
        }
      
        $re = call_user_func(array($this -> {$model}, $method), ...$params);
        return $ctx -> res(['code'=>0, 'data' => $re]);
    }



    function leave($ctx)
    {
        //do nothing
    }
}
?>
