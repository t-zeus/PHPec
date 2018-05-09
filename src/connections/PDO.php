<?php
namespace PHPec\connections;

class PDO extends Base
{
    //创建连接
    function conn($db, $persistent)
    {
        if (empty($db['dsn']) || empty($db['user']) || !isset($db['password'])) {
            trigger_error("PDO Error: miss connect param", E_USER_ERROR);
        }
        $options = [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION];
        if ($persistent) { //持久链接
            $options[\PDO::ATTR_PERSISTENT] = true;
        }
        return new \PDO($db['dsn'], $db['user'], $db['password'], $options);
    }
}