<?php
use PHPUnit\Framework\TestCase;
//test Router direct
define('APP_PATH',__DIR__.'/../example');

include_once('../src/App.php');
include_once('../src/middleware/router.php');

function setRouter($ctx,$r){
	$ctx ->route_param = [
		'type' 		=> $r[0],
		'method' 	=> $r[1],
		'pathinfo'	=> $r[2],
		'query'		=> $r[3]
	];
}

class RouterTest extends TestCase{

	function testNew(){
		$app =  new PHPec\App();
		$app -> use();//skip router;
		$app -> run();
		$this -> assertEquals(1,$app->route_param['type']);
		$this -> assertEquals('get',$app->route_param['method']);
		$this -> assertEquals(NULL,$app->route_param['pathinfo']);
		$this -> assertEquals('',$app->route_param['query']);
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
	function testNotSupportMethod($app){
		setRouter($app,array(1,'options','/User/shw','c=User&a=profile'));
		$app -> allowedMethod = ['post','get']; //coverage fllow case
		$this -> router -> begin($app);
		$this -> assertEquals('Method not allowed',$app->body);
		$this -> assertEquals(405,$app->status);
	}
	/**
	 *@depends testNew
	 */
	function testResourceNameInvalid($app){
		setRouter($app,array(2,'post','/anvalid/Aa',''));
		$this -> router -> begin($app);
		$this -> assertEquals('Resource name invalid',$app->body);
		$this -> assertEquals(404,$app->status);
	}
	/**

	 *@depends testNew
	 */
	function testClassInvalid($app){
		setRouter($app,array(2,'post','/ClassInvalid/aa',''));
		$this -> router -> begin($app);
		$this -> assertEquals('Resource class not found --ClassInvalid',$app->body);
		$this -> assertEquals(404,$app->status);
	}
	/**
	 *@depends testNew
	 */
	function testActionNameInvalid($app){
		setRouter($app,array(2,'post','/Invalid/Aa',''));
		$this -> router -> begin($app);
		$this -> assertEquals('action name invalid',$app->body);
		$this -> assertEquals(404,$app->status);
	}
	/**
	 *@depends testNew
	 */
	function testActionNotFound($app){
		setRouter($app,array(2,'post','/ShopProduct/aa',''));
		$this -> router -> begin($app);
		$this -> assertEquals('action not found',$app->body);
		$this -> assertEquals(404,$app->status);
	}


	//type=1 for querystring	

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
	function testQueryEmpty($app){
		setRouter($app,array(1,'get','/User/shw',''));
		$this -> router -> begin($app);
		$this -> assertEquals('[before]Any->_any[after]',$app->body);
	}
	/**
	 *@depends testNew
	 */
	function testQueryPost($app){
		setRouter($app,array(1,'post','/User/shw','c=User&a=profile'));
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
		$this -> assertEquals('[before]Any->_any[after]',$app->body);
	}
	
	//type=2 for pathinfo

	/**
	 *@depends testNew
	 */
	function testPathEmpty($app){
		setRouter($app,array(2,'get','/',''));
		$this -> router -> begin($app);
		$this -> assertEquals('[before]Any->_any[after]',$app->body);
	}
	/**
	 *@depends testNew
	 *@expectedException  Exception
	 *@expectedExceptionMessage PATH_INFO invalid
	 */
	function testPathNull($app){
		setRouter($app,array(2,'get',null,''));
		$this -> router -> begin($app);
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
		$this -> assertEquals('[before]Any->_any[after]',$app->body);
	}

	//type=3 for RESTful

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
	 */
	function testRestPost($app){
		setRouter($app,array(3,'post','/User/aa',''));
		$this -> router -> begin($app);
		$this -> assertEquals('User->post',$app->body);
	}
	/**
	 *@depends testNew
	 */
	function testRestAndId($app){
		setRouter($app,array(3,'post','/User/123',''));
		$this -> router -> begin($app);
		$this -> assertEquals('User->post',$app->body);
		$this -> assertEquals(['User'=>123],$app->resId);
	}
	/**
	 *@depends testNew
	 */
	function testRestNestAndId($app){
		setRouter($app,array(3,'post','/Shop/123/Product/345',''));
		$this -> router -> begin($app);
		$this -> assertEquals('/Shop/Product->post',$app->body);
		$this -> assertEquals(['Shop'=>123,'Product'=>345],$app->resId);
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
	function testRestAnyResourceAnyAction($app){
		setRouter($app,array(3,'post','/Show/show',''));
		$this -> router -> begin($app);
		$this -> assertEquals('[before]Any->_any[after]',$app->body);
	}
}
?>
