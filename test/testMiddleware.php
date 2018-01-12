<?php
use PHPUnit\Framework\TestCase;
define('APP_PATH',__DIR__.'/../example');
include '../core.php';

//for app->use
class MyM3 implements \PHPec\Middleware{
	function begin($ctx){
		$ctx -> my.= 'hello ';
	}
	function end($ctx){
		$ctx -> my .= 'world';

	}
}

class MiddlewareTest extends TestCase{

	function testNew(){
		$app = new PHPec();
		$this -> assertEquals('[PHPec Appp]',$app);
		return $app;
	}
	/**
	 *@depends testNew
	 *@expectedException  Exception
	 *@expectedExceptionMessage call not defined method: PHPec -> notExists
	 */
	function testNotExistsMethod($app){
		$app -> notExists();
	}
	/**
	 *@depends testNew
	 *@expectedException  Exception
	 *@expectedExceptionMessage load middleware file fail
	 */
	function testMiddlewareFileNotFound($app){
		$app -> use('NotExists');
	}
	/**
	 *@depends testNew
	 *@expectedException  Exception
	 *@expectedExceptionMessage middleware invalid: type error

	 */
	function testMiddlewareInvalidType($app){
		$app -> use([['m1']]);
	}
	/**
	 *@depends testNew
	 *@expectedException  Exception
	 *@expectedExceptionMessage middleware invalid: class or function not found
	 */

	function testdMiddlewareClassNotFound($app){
		$app -> use('ClassNotFound');

	}
	/**
	 *@depends testNew
	 *@expectedException  Exception
	 *@expectedExceptionMessage middleware invalid: ClassInvalid not implements \PHPec\Middleware
	 */
	function testMiddlewareClassInvalid($app){
		$app -> use('ClassInvalid');
	}
	/**
	 *@depends testNew
	 *@expectedException  Exception
	 *@expectedExceptionMessage middleware invalid:stdClass not implement \PHPec\Middleware 
	 */
	function testMiddlewareClassInvalid2($app){
		$app -> use(new stdClass);
	}
	/**
	 *@depends testNew
	 */
	function testAddMiddleware($app){
		$app -> use(function($ctx){
			$ctx -> text = '[begin]';
			$ctx -> next();
			$ctx -> text.= '[end]';
			if(!$ctx ->body){
				$ctx->body=$ctx->text;
			}
		});
		$app -> use(new MyM3());
		$app -> use(['M1','M2']);
		$app -> use();//skip Router

		$app -> use('M1'); //donot exec
		$app -> run();
		$this -> assertEquals(1,$app->router['type']);
		$this -> assertEquals('get',$app->router['method']);
		$this -> assertEquals('/',$app->router['pathinfo']);
		$this -> assertEquals('',$app->router['query']);
		$this -> assertEquals('hello world',$app->my); //form MyM3
		$this -> assertEquals('[begin]>m1>m2>m2 end>m1 end[end]',$app->text);
		$this -> assertEquals('[begin]>m1>m2>m2 end>m1 end[end]',$app->body);
		$this -> expectOutputString('[begin]>m1>m2>m2 end>m1 end[end]');
	}
}

?>
