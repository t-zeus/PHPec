<?php
namespace PHPec\component;

//pdo连接对象
class PDOConn {
    use \PHPec\DITrait;

    public function getConn()
    {
        $db  = $this -> Config -> get('db');
        if (empty($db['dsn']) || empty($db['user']) || !isset($db['password'])) {
            trigger_error("PDO Error: miss connect param", E_USER_ERROR);
        }
        $dbh = new \PDO($db['dsn'], $db['user'], $db['password']);
        $dbh -> setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        return $dbh;
    }
}