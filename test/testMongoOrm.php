<?php
use PHPUnit\Framework\TestCase;

require '../src/App.php';

define('MONGO_DSN','mongodb://127.0.0.1:27017');
define('MONGO_DBNAME','user');

require '../src/middleware/mongo_orm.php';

class MongoOrmTest extends TestCase {
	//连接
	function testCreate(){
		$orm = new \PHPec\MongoOrm();
		$re = $orm -> exec(['drop'=>'test']);
		$this -> assertEquals(1, $re[0] -> ok);
		return $orm;
	}

	/**
	 *@depends testCreate
	 *@expectedException  Exception
	 */
	function testDataUnexpect($orm){
		$re = $orm -> exec(['aaa']);
	}
	/**
	 *@depends testCreate
	 */
	function testMap($orm){
		$obj = $orm -> map('user.test');
		$this -> assertTrue($obj instanceof \PHPec\MongoDao);
		return $obj;
	}

	/**
	 *@depends testMap
	 */
	function testCRUD($obj){
		$ids = [];
		$id = $obj -> add(['name'=>'day day up','author'=>'Tim','nums'=>11]);
		$this -> assertFalse($id == false);
		$ids[]=$id;

		$re = $obj -> getOne(["_id" => $id]);
		$this -> assertEquals(11,$re['nums']);
		$this -> assertEquals('day day up',$re['name']);
		$this -> assertEquals('Tim',$re['author']);

		$re['name'] = 'good good study';
		$re2 = $obj -> update(["_id"=>$id],$re);
		$this -> assertEquals(1,$re2);

		$re3 = $obj -> getOne(["_id"=>$id]);
		$this -> assertEquals('good good study',$re3['name']);
		$this -> assertEquals($re['author'],$re3['author']);

		$id = $obj -> add(['name'=>'day day up 2','author'=>'Tim 2','nums'=>10]);
		$ids[] = $id;

		$re = $obj -> get(['nums'=>11]);
		$this -> assertEquals(1,count($re));

		$re = $obj -> get(["nums" => ['$in' => [10,11]]]);
		$this -> assertEquals(2,count($re));

		$re = $obj -> delete(["nums" =>11]);
		$this -> assertEquals(1,$re);

		$re = $obj -> delete(["nums" => ['$gt' => 1]]); 
		$this -> assertEquals(1,$re);

		$re = $obj -> get([]);
		$this -> assertEquals(0,count($re));

	}
}
?>