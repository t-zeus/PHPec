<?php
namespace PHPec;

/**
 * 自动注入
 * 未绑定时，查找顺序为 APP_PATH/service目录、PHPec/component目录
 * 在配置中使用 container_bind进行指定，格式：
 * container_bind = ['Interface' = > 'fulName class of Impl']
 */
Trait DITrait
{   
    private $native = [
        'Logger' => '\PHPec\interfaces\Logger',
        'Config' => '\PHPec\interfaces\Config',
        'Auth'   => '\PHPec\interfaces\Auth'
    ];
    public function __get($k)
    {
        $objs = Container::getInstance();
        if (isset( $objs -> $k)) {
            return  $objs -> $k;
        }
        if (empty($objs -> Config)) {  //Config is inject default
            $config = APP_PATH."/service/Config.php";
            if(file_exists($config)) {
                require $config;
                $confClass = APP_NS. '\service\Config';
                $conf = new $confClass;
            } else{
                $conf = new component\Config();
            }
            if (! $conf instanceof interfaces\Config) {
                trigger_error("DI fail, Config must implement \\PHPec\\interfaces\\Config", E_USER_ERROR);
            }
            
            $objs -> Config = $conf;
        }
        if (preg_match('/^[A-Z]/', $k)) {
            $class_map = $objs -> Config -> get('container_bind');
            $interface = null;
            if (!empty($class_map[$k])) { //有绑定, interface => classImpl
                $class  = $class_map[$k];
                $interface = APP_NS."\\interfaces\\".$k;
            } else {
                if (substr($k, -5) == 'Model') { //*Model
                    $class = '\\PHPec\\component\\PDO';
                    $table = strtolower(substr($k, 0, -5));
                    $obj = new $class($table); //todo:SCHEMA
                    $objs -> $k = $obj;
                    return $obj;
                }
                $file = APP_PATH. "/service/{$k}.php";
                if (file_exists($file)) {
                    require $file;
                    $class = APP_NS .'\\service\\'.$k;
                } else {
                    $class = '\\PHPec\\component\\'.$k;
                }
            }
            if (isset($this -> native[$k])) {
                $interface = $this -> native[$k];
            }
            $obj =  new $class;
            if ($interface) {
                if(! $obj instanceof $interface) {
                    trigger_error("DI fail, $class must implement $interface", E_USER_ERROR);
                }
            }
            $objs -> $k = $obj;
            return $obj;
        }
    }
    // function __set($k,$v){
    //     $this -> $k = $v;
    // }
}