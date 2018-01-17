<?php
namespace PHPec;
class MysqlOrm implements Middleware{
    function map($table){
        return new Dao($table);
    }
    function query($sql,$params){
        return Dao::query($sql,$parms);
    }
    function trans($query){
        Dao::$dbh ->beginTransaction();
        $query(); // $dbh->exec();
        Dao::$dbh -> commit();
        //rollback
        //判断是否成功
    }

    function begin($ctx){
        $ctx -> orm = $this;
    }
    function end($ctx){

    }
}

class Dao{
    private $table = '';
    static $dbh = null;
    function __construct($table){
        $this -> table = $table;
    }
    static function connect(){
        if(!self::$dbh) {
            self::$dbh = new PDO(DB_DSN, DB_USER, DB_PASS);
        }
    }

    static function query($sql,$param = null){
        self::connect();
        $stmt = self::$dbh -> prepare($sql);
        $ph = substr_count($sql,"?");
        if($ph > 0 && $param != null){
            if(!is_array($param)) $param = [$param];
            if($ph != count($param)){
                throw new \Exception('PHPec MysqlOrm Error: placeholder not match',1);
            }
            foreach($param as $k => $v){
                $stmt -> bindParam($k+1,$param[$k],self::_getType($v));
            }
        }
        if($stmt->execute()){
            if(strtolower(substr($sql,0,6)) == 'select'){
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            else return $stmt->rowCount();
        }else{
            return false;
        }
    }
    //增加一条记录，返回false or insertId
    function add($data){
        $this -> _checkTable();
        $d = self::_buildData($data);
        $sql = sprintf("insert into `%s` set %s",$this -> table, $d[0]);
        $result = self::query($sql,$d[1]);
        if($result){
            return self::$dbh -> lastInsertId();
        }
        return false;
    }
    function delete($where){
        $this -> _checkTable();
        $w = self::_buildWhere($where);
        $sql = sprintf('delete from `%s` where %s',$this -> table, $w[0]);
        $param = isset($w[1]) ? $w[1] : null;
        return self::query($sql,$param);
    }
    function update($where,$data){
        $this -> _checkTable();
        $d = self::_buildData($data);
        $w = self::_buildWhere($where);
        $params = $d[1];
        if(isset($w[1])) $params = array_merge($params,$w[1]);
        $sql = sprintf('update `%s` set %s where %s',$this -> table, $d[0],$w[0]);
        return self::query($sql, $params);
    }
    //查询数据，默认限制为page=1,pageSize=20
    function get($where,$options = []){
        $this -> _checkTable();
        $w = self::_buildWhere($where);
        $fields = isset($options['fields']) ? $options['fields'] : '*';
        $sort = '';
        if(isset($options['sort'])){
            if(!is_string($options['sort'])) throw new \Exception('PHPec MysqlOrm Error: options sort invalid',1);
            $sort = 'order by '.$options['sort'];
        }
        $page     = isset($options['page']) ? intval($options['page']) :  1;
        $pageSize = isset($options['pageSize']) ? intval($options['pageSize']) : 20;
        $limit = sprintf(" limit %d,%d",($page-1)*$pageSize,$pageSize);
        $sql = sprintf("select %s from %s where %s%s%s",$fields,$this -> table,$w[0],$sort,$limit);
        $params = isset($w[1]) ? $w[1] : null;
        return self::query($sql,$params);
    }
    //查询数据，只获取一行
    function getOne($where,$options =[]){
        $this -> _checkTable();
        $w = self::_buildWhere($where);
        $fields = isset($options['fields']) ? $options['fields'] : '*';
        $sort = '';
        if(isset($options['sort'])){
            if(!is_string($options['sort'])) throw new \Exception('PHPec MysqlOrm Error: options sort invalid',1);
            $sort = 'order by '.$options['sort'];
        }
        $sql = sprintf("select %s from %s where %s%s limit 1",$fields,$this -> table,$w[0],$sort);
        $params = isset($w[1]) ? $w[1] : null;
        $result = self::query($sql,$params);
        return isset($result[0]) ? $result[0] : $result;
    }
    function _checkTable(){
        if(!$this -> table){
            throw new \Exception("PHPec MysqlOrm error:  table not yet set", 1);
        }
    }
    /**
     * 处理where条件,支持两种模式，
     * 1. 直接使用字符串作为条件表达式，比如 a=1，a>=2 
     *     a. 只支持一个条件，运算符支持 >,<,>=,<=,<>,=, is, is not, like, not like, in,not in
     *     b. is/is not 只能是null
     *     c. 值不用引号，包括 like/not like
     *     d. in/not in 用逗号分隔，如： a in 1,2,3
     * 2. 使用占位符及占位内容，如： ['a=? and b=?',[1,2]]
     *     a. 数组第一个为带占位符的完整条件表达式
     *     b. 第二个元素为用来替换占位符的内容数组
     *     c. 注意保证占位数量和替换内容的个数一样
     */
    static function _buildWhere($where){
        if(is_string($where)){
            $p = "/(>=|<=|<>|=|>|<| is not | is | in | not in | like | not like )/i";
            $arrs = preg_split($p,$where,2,PREG_SPLIT_DELIM_CAPTURE);
            if(count($arrs) != 3){
                throw new \Exception('PHPec MysqlOrm Error: $where exp invalid',1);
            }
            $op = strtolower($arrs[1]);
            if($op == ' in ' || $op == ' not in '){

            }elseif($op == ' is ' || $op == ' is not '){
                return [$arrs[0].$op." null"];
            }else{
                $params = $arrs[2];
                if(!is_array($params)) $params = [$params];
                return [$arrs[0].$op."?", $params];
            }
        }elseif(is_array($where) && count($where) == 2){
            if(substr_count($where[0],"?") != count($where[1])){
                throw new \Exception('PHPec MysqlOrm Error: $where exp invalid -- placeholder not match',1);
            }
            return $where;
        }else{
            throw new \Exception('PHPec MysqlOrm Error: $where exp invalid -- placeholder not match',1);
        }
    }
    //处理insert或update的data,['a'=>'b','a1'=>'b2'] => ['a=?,a1=?',['b','b2']]
    static function _buildData($data){
        if(!is_array($data) || empty($data)) { 
            throw new \Exception("PHPec MysqlOrm Error: \$data must be a array", 1);
        }
        $fields = $params = [];
        foreach($data as $k => $v){
            if(is_numeric($k)){
                throw new \Exception("PHPec MysqlOrm Error: field name of \$data unexpected", 1);
            }
            $fields[] = "`$k` = ?";
            $params[] = $v;
        }
        return [implode(",",$fields),$params];
    }
    static function _getType($val){
        $type = PDO::PARAM_STR;
        if(is_bool($val))      $type = PDO::PARAM_BOOL;
        elseif(is_int($val))   $type = PDO::PARAM_INT;
        elseif(is_null($val))  $type = PDO::PARAM_NULL;
        return $type;
    }
}

?>