<?php
namespace PHPec\connections;

//连接抽象类
abstract class Base
{
    use \PHPec\DITrait;

    protected $handle = [];
    /**
     * 建立连接(由具体连接类实现)
     * 
     * @param array $target 连接配置，根椐具体的服务类型有不同
     * @param boolean $persistent 是否持久连接
     * @return Resource $con 连接句柄
     */
    abstract protected function conn($target, $persistent);

    /**
     * 连接管理，返回已有连接或创建并返回，具体类可直接使用或重写
     *
     * @param string $type M|S,  获取的连接类型（主或从）
     */
    public function getConn($type)
    {
        $sClass = get_called_class();
        $key    = strtolower(substr($sClass, strrpos($sClass, "\\")+1));
        if (empty($this -> handle[$type])) {
            $store = [];
            $conf       = $this -> Config -> get($key);
            $persistent = $this -> Config -> get($key.".persistent", false);
            if ($type == 'M') {
                $target = empty($conf['master']) ? $conf : $conf['master'];
                $store = ['M'];
            } elseif ($type == 'S') {
                if (!empty($conf['slave'])) {
                    $target = $conf['slave'];
                    $store = ['S']; 
                } else { //未配置从，如果已有主使用主，否则创建链接
                    if (!empty($this -> handle['M'])) $this -> handle['S'] = $this -> handle['M'];
                    else { 
                        $target = empty($conf['master']) ? $conf : $conf['master'];
                        $store = ['M','S'];
                    }
                }
            }
            $this -> handle = array_fill_keys($store, $this -> conn($this -> select($target), $persistent));
        }
        return $this -> handle[$type];
    }

    //从多个连接参数中选择一个，默认为随机，如有需要，可重写为其它方式
    protected function select($target)
    {
        if (!empty($target[0]) && is_array($target[0])) {
            $idx = array_rand($target);
            $target = $target[$idx];
        }
        return $target;
    }
}