<?php
use PHPUnit\Framework\TestCase;
//test Router direct
define('APP_PATH',__DIR__.'/../example');

include_once('../core.php');
include_once('../middleware/router.php');

function setRouter($ctx,$r){
	$ctx ->router = [
		'type' 		=> $r[0],
		'method' 	=> $r[1],
		'pathinfo'	=> $r[2],
		'query'		=> $r[3]
	];
}

class RouterTest extends TestCase{

	function testNew(){
		$app =  new PHPec();
		$app -> use();//skip router;
		$app -> run();
		$this -> assertEquals(1,$app->router['type']);
		$this -> assertEquals('get',$app->router['method']);
		$this -> assertEquals('/',$app->router['pathinfo']);
		$this -> assertEquals('',$app->router['query']);
		return $app;
	}
	function setUp(){
		$this -> router=new \PHPec\Router();
	}
	/**
	 *@depends testNew
	 *@expectedException  Exception
	 *@expectedExceptionMessage router param miss
	 */
	function testMissType($app){
		setRouter($app,array('','get','/User/shw','c=User&a=profile'));
		$this -> router -> begin($app);
	}
	/**
	 *@depends testNew
	 *@expectedException  Exception
	 *@expectedExceptionMessage router param miss
	 */
	function testMissMethod($app){
		setRouter($app,array(1,'','/User/shw','c=User&a=profile'));
		$this -> router -> begin($app);
	}
	/**
	 *@depends testNew
	 */
	function testQuery($app){
		setRouter($app,array(1,'get','/User/shw','c=User&a=profile'));
		$this -> router -> begin($app);
		$this -> assertEquals('User->profile',$app->body);
	}
	/**
	 *@depends testNew
	 */
	function testQueryAnyAction($app){
		setRouter($app,array(1,'get','/User/shw','c=User&a=none'));
		$this -> router -> begin($app);
		$this -> assertEquals('User->_any',$app->body);
	}
	/**
	 *@depends testNew
	 */
	function testQueryAnyResource($app){
		setRouter($app,array(1,'get','/User/shw','c=None&a=show'));
		$this -> router -> begin($app);
		$this -> assertEquals('[before]Any->show[after]',$app->body);
	}
	/**
	 *@depends testNew
	 */
	function testQueryAnyResourceAnyAction($app){
		setRouter($app,array(1,'get','/User/shw','c=None&a=none'));
		$this -> router -> begin($app);
		$this -> assertEquals('action not found[after]',$app->body);
		$this -> assertEquals(404,$app->status);
	}


	/**
	 *@depends testNew
	 */
	function testPath($app){
		setRouter($app,array(2,'get','/User/profile',''));
		$this -> router -> begin($app);
		$this -> assertEquals('User->profile',$app->body);
	}
	/**
	 *@depends testNew
	 *@expectedException  Exception
	 *@expectedExceptionMessage PATH_INFO invalid
	 */
	function testPathNull($app){
		setRouter($app,array(2,'get',null,''));
		$this -> router -> begin($app);
		$this -> assertEquals('User->profile',$app->body);
	}
	/**
	 *@depends testNew
	 */
	function testPathAnyAction($app){
		setRouter($app,array(2,'get','/User/shw',''));
		$this -> router -> begin($app);
		$this -> assertEquals('User->_any',$app->body);
	}
	/**
	 *@depends testNew
	 */
	function testPathAnyResource($app){
		setRouter($app,array(2,'get','/Show/show',''));
		$this -> router -> begin($app);
		$this -> assertEquals('[before]Any->show[after]',$app->body);
	}
	/**
	 *@depends testNew
	 */
	function testPathAnyResourceAnyAction($app){
		setRouter($app,array(2,'get','/Shop/shw',''));
		$this -> router -> begin($app);
		$this -> assertEquals('action not found[after]',$app->body);
		$this -> assertEquals(404,$app->status);
	}


	/**
	 *@depends testNew
	 */
	function testRestGet($app){
		setRouter($app,array(3,'get','/User/profile',''));
		$this -> router -> begin($app);
		$this -> assertEquals('User->_any',$app->body);
	}
	/**
	 *@depends testNew
	 *@expectedException  Exception
	 *@expectedExceptionMessage PATH_INFO invalid
	 */
	function testRestPost($app){
		setRouter($app,array(3,'post','/User/aa',''));
		$this -> router -> begin($app);
		$this -> assertEquals('User->post',$app->body);
	}
	/**
	 *@depends testNew
	 */
	function testRestPostAnyRes($app){
		setRouter($app,array(2,'get','/None/get',''));
		$this -> router -> begin($app);
		$this -> assertEquals('[before]Any->get[after]',$app->body);
	}
	/**
	 *@depends testNew
	 */
	function testPathAnyResourceNotAction($app){
		setRouter($app,array(3,'post','/Show/show',''));
		$this -> router -> begin($app);
		$this -> assertEquals('action not found[after]',$app->body);
	}

	/**
	 *@depends testNew
	 */
	function testRestClassInvalid($app){
		setRouter($app,array(3,'post','/Invalid/aa',''));
		$this -> router -> begin($app);
		$this -> assertEquals('Resource class not found --Invalid',$app->body);
		$this -> assertEquals(404,$app->status);
	}
}
?>