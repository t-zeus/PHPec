<?php
use PHPUnit\Framework\TestCase;
define('APP_PATH',__DIR__.'/../example');
include '../core.php';
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
	 *@expectedExceptionMessage middleware class or function not found
	 */
	function testdMiddlewareClassNotFound($app){
		$app -> use('ClassNotFound');
	}
	/**
	 *@depends testNew
	 *@expectedException  Exception
	 *@expectedExceptionMessage middleware ClassInvalid invalid
	 */
	function testMiddlewareClassInvalid($app){
		$app -> use('ClassInvalid');
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
		$app -> use('M1');
		$app -> use('M2');
		$app -> use();//skip Router
		$app -> use('M1'); //donot exec
		$app -> run();
		$this -> assertEquals(1,$app->router['type']);
		$this -> assertEquals('get',$app->router['method']);
		$this -> assertEquals('/',$app->router['pathinfo']);
		$this -> assertEquals('',$app->router['query']);
		$this -> assertEquals('[begin]>m1>m2>m2 end>m1 end[end]',$app->text);
		$this -> assertEquals('[begin]>m1>m2>m2 end>m1 end[end]',$app->body);
		$this -> expectOutputString('[begin]>m1>m2>m2 end>m1 end[end]');
	}
}
?>