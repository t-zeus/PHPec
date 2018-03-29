<?php
namespace PHPec\component;

//pdo连接对象
class PDOConn extends \PDO {
    use \PHPec\DITrait;

    public function __construct()
    {
        $this -> Logger -> debug('PDOConn...');
        $db  = $this -> Config -> get('db');
        if (empty($db['dsn']) || empty($db['user']) || !isset($db['password'])) {
            trigger_error("PDO Error: miss connect param", E_USER_ERROR);
        }
        parent::__construct($db['dsn'], $db['user'], $db['password']);
        $this -> setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }
}