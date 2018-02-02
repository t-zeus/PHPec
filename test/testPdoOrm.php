<?php
use PHPUnit\Framework\TestCase;
//test Router direct
define('APP_PATH',__DIR__.'/../example');

require '../src/App.php';
require '../src/middleware/pdo_orm.php';

define('DB_DSN','mysql:host=127.0.0.1;dbname=test');
define('DB_USER','root');
define('DB_PASS','root');

class PdoOrmTest extends TestCase {

	//连接,创建测试库
	function testCreate(){
		$orm = new \PHPec\PdoOrm();
		$re1 = $orm -> query("drop table if exists phpec_unittest");
		$sql = 'CREATE TABLE  `phpec_unittest`(
			   `id` INT UNSIGNED AUTO_INCREMENT,
			   `name` VARCHAR(100) NOT NULL,
			   `author` VARCHAR(40) NOT NULL,
			   `nums` INT,
			   PRIMARY KEY ( `id` )
			  )ENGINE=InnoDB DEFAULT CHARSET=utf8;';

		$re2 = $orm -> query($sql);
		$this -> assertTrue($re1!==false);
		$this -> assertTrue($re2!==false);
		return $orm;
	}
	/**
	 *@depends testCreate
	 *@expectedException  PDOException
	 */
	function testQueryInvalidSQL($orm){
		$orm -> query("invalid sql");
	}
	/**
	 *@depends testCreate
	 *@expectedException  PDOException
	 *@expectedExceptionMessage SQLSTATE[42S02]: Base table or view not found: 1146 Table 'test.not_exists_table' doesn't exist
	 */
	function testQueryException($orm){
		$orm -> query("delete from not_exists_table");
	}
	/**
	 *@depends testCreate
	 *@expectedException  Exception
	 *@expectedExceptionMessage PHPec MysqlOrm Error: placeholder not match
	 */
	function testQueryNotMatch($orm){
		$orm -> query("select * from phpec_unittest where id=? or id=?", 1);
	}
	/**
	 *@depends testCreate
	 */
	function testQuery($orm){
		//insert
		$re = $orm -> query("insert into phpec_unittest set name=?,author=?,nums=?",['day day up','Tim',22]);
		$this -> assertEquals(1,$re);
		//insert another
		$orm -> query("insert into phpec_unittest set name=?,author=?,nums=?",['good good study','Kate',2]);
		//get
		$re = $orm -> query("select * from phpec_unittest where author=?",'Tim');
		$this -> assertEquals('day day up',$re[0]['name']);
		$this -> assertEquals('Tim',$re[0]['author']);
		$this -> assertEquals(22,$re[0]['nums']);
		$re = $orm -> query("select * from phpec_unittest where author=?",'Kate');
		$this -> assertEquals('good good study',$re[0]['name']);
		//update
		$re = $orm -> query("update phpec_unittest set author=? where id=?",['Kitty',$re[0]['id']]);
		$this -> assertEquals(1,$re);
		$re = $orm -> query("update phpec_unittest set author=? where id=?",['Kitty',$re[0]['id']]);
		$this -> assertEquals(0,$re);
		$re = $orm -> query("select * from phpec_unittest where author=?",'Kate');
		$this -> assertEquals([],$re);
		$re = $orm -> query("select * from phpec_unittest where author=?",'Kitty');
		$this -> assertEquals('good good study',$re[0]['name']);
		$re = $orm -> query("delete from phpec_unittest where id>?", 0);
		$this -> assertEquals(2,$re);
		$re = $orm -> query("select count(*) as num from phpec_unittest");
		$this -> assertEquals(0,$re[0]['num']);
	}
	/**
	 *@depends testCreate
	 */
	function testMap($orm){
		$obj = $orm -> map('phpec_unittest');
		$this -> assertTrue($obj instanceof \PHPec\Dao);
		return $obj;
	}
	/**
	 *@depends testMap
	 */
	function testInsert($obj){
		$id = $obj -> add(['name'=>'day day up','author'=>'Tim','nums'=>11]);
		$this -> assertTrue($id > 0);
		$re = $obj -> getOne("id={$id}");
		$this -> assertEquals(11,$re['nums']);
		$this -> assertEquals('day day up',$re['name']);
		$this -> assertEquals('Tim',$re['author']);
	}
	/**
	 *@depends testMap
	 */
	function testCRUD($obj){
		$ids = [];
		$id = $obj -> add(['name'=>'day day up','author'=>'Tim','nums'=>11]);
		$this -> assertTrue($id > 0);
		$ids[]=$id;

		$re = $obj -> getOne("id={$id}");
		$this -> assertEquals(11,$re['nums']);
		$this -> assertEquals('day day up',$re['name']);
		$this -> assertEquals('Tim',$re['author']);

		$re['name'] = 'good good study';
		$re2 = $obj -> update("id=$id",$re);
		$this -> assertEquals(1,$re2);

		$re3 = $obj -> getOne(["id=?",$id]);
		$this -> assertEquals('good good study',$re3['name']);
		$this -> assertEquals($re['author'],$re3['author']);

		$id = $obj -> add(['name'=>'day day up 2','author'=>'Tim 2','nums'=>10]);
		$ids[] = $id;

		$re = $obj -> get(["id in (?)",[$ids]]);
		$this -> assertEquals(2,count($re));

		$re = $obj -> get("nums in 10,11");
		$this -> assertEquals(3,count($re));

		$re = $obj -> delete(["nums = ?",11]);
		$this -> assertEquals(2,$re);

		$re = $obj -> delete(["nums in (?)",[10]]); 
		$this -> assertEquals(1,$re);

		$re = $obj -> get("1=1");
		$this -> assertEquals(0,count($re));

	}
	/**
	 *@depends testCreate
	 *@depends testMap
	 */
	function testTransRollback($orm,$obj){
		$re = $orm -> transaction(function(&$err) use ($obj){
			$obj -> add(['name'=>'day day up','author'=>'Tim','nums'=>11]);
			$obj -> add(['names'=>'day day up','author'=>'Tim','nums'=>11]); //error
		});
		$this -> assertTrue($re==false);
		$re = $obj -> get("1=1");
		$this -> assertEquals(0,count($re));

		//手工rollback
		$re = $orm -> transaction(function(&$err) use ($obj){
			$obj -> add(['name'=>'day day up','author'=>'Tim','nums'=>11]);
			return false;
		});
		$this -> assertTrue($re==false);
		$re = $obj -> get("1=1");
		$this -> assertEquals(0,count($re));
	}
	/**
	 *@depends testCreate
	 *@depends testMap
	 */
	function testTransCommit($orm,$obj){
		$re = $orm -> transaction(function(&$err) use ($obj){
			$obj -> add(['name'=>'day day up','author'=>'Tim1','nums'=>1]);
			$obj -> add(['name'=>'day day up','author'=>'Tim2','nums'=>2]); 
		});
		$this -> assertTrue($re);

		$re = $obj -> getOne(["nums>=?",1]);
		$this -> assertEquals('Tim1',$re['author']);

		$re = $obj -> getOne(["nums>?",1],['sort'=>'nums desc']);
		$this -> assertEquals('Tim2',$re['author']);

	}
	
}
?>