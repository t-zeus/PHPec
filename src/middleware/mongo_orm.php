<?php
namespace PHPec;

defined('MONGO_DSN') || exit('MONGO_DSN not defined');
defined('MONGO_DBNAME') || exit('MONGO_DBNAME not defined');

class MongoOrm implements Middleware{
    function map(String $coll,Array $schema = []){
        return new MongoDao($coll,$schema);
    }
    function exec(Array $cmd){
        return MongoDao::exec($cmd);
    }
    function begin($ctx){
        $ctx -> mongo = $this;
    }
    function end($ctx){
    }
}

class MongoDao{
    private $coll = '';
    static $dbh = null;
    function __construct($coll,$schema){
        $this -> coll = $coll;
        $this -> writeConcern = new \MongoDB\Driver\WriteConcern(\MongoDB\Driver\WriteConcern::MAJORITY, 1000);
        //todo: schema check
    }
    static function connect(){
        if(!self::$dbh) {
            self::$dbh = new \MongoDB\Driver\Manager(MONGO_DSN);
        }
    }
    static function exec($cmd){
        self::connect();
        $cursor = self::$dbh -> executeCommand(MONGO_DBNAME,  new \MongoDB\Driver\Command($cmd));
        return $cursor -> toArray();
    }
    //增加一条记录，返回false or insertId
    function add(Array $data){
        $this -> _checkColl();
        self::connect();
        $d = $this -> _checkData($data);
        $bulk = new \MongoDB\Driver\BulkWrite;
        $id = $bulk -> insert($d);
        $result = self::$dbh -> executeBulkWrite($this->coll, $bulk, $this -> writeConcern);
        $err = $result -> getWriteErrors();
        if(empty($err)){
            return $id;
        }else{
            return false;
        }
    }
    //删除，返回被删除的记录数，失败返回false
    function delete($filter){
        $this -> _checkColl();
        self::connect();
        $bulk = new \MongoDB\Driver\BulkWrite;
        $id = $bulk -> delete($filter, ['limit'=>0]);
        $result = self::$dbh -> executeBulkWrite($this->coll, $bulk, $this -> writeConcern);
        $err = $result -> getWriteErrors();
        if(empty($err)){
            return $result -> getDeletedCount();
        }else{
            return false;
        }
    }
    function update($filter,Array $data){
        $this -> _checkColl();
        self::connect();
        $d = $this -> _checkData($data);

        $bulk = new \MongoDB\Driver\BulkWrite;
        $id = $bulk -> update($filter, ['$set' => $d], ['multi' => true, 'upsert' => false]);
        $result = self::$dbh -> executeBulkWrite($this->coll, $bulk, $this -> writeConcern);
        $err = $result -> getWriteErrors();
        if(empty($err)){
            return $result -> getModifiedCount();
        }else{
            return false;
        }
    }
    //查询数据，默认限制为page=1,pageSize=20
    function get($filter,Array $options = []){
        $this -> _checkColl();
        self::connect();
        $query = new \MongoDB\Driver\Query($filter, $options);
        $cursor = self::$dbh -> executeQuery($this -> coll, $query);
        return $cursor -> toArray();
    }
    //查询数据，只获取一行,options=>['sort'=>,fields='']
    function getOne($filter,Array $options =[]){
        $this -> _checkColl();
        self::connect();
        $options['limit'] = 1;
        $query = new \MongoDB\Driver\Query($filter, $options);
        $cursor = self::$dbh -> executeQuery($this -> coll, $query);
        $re = $cursor -> toArray();
        return empty($re) ? [] : (array)$re[0];
    }
    function _checkColl(){
        if(!$this -> coll){
            throw new \Exception("PHPec MongoOrm error:  collection not yet set", 1);
        }
    }
    //处理insert或update的data,返回处理后的data数组，如果失败抛异常
    static function _checkData($data){
        if(!is_array($data) || empty($data)) { 
            throw new \Exception("PHPec MongoOrm Error: \$data must be a array", 1);
        }
        //todo:根椐schema判断data以及补充默认值
        return $data;
    }
}
?>