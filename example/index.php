<?php
error_reporting(E_ALL);
//example for PHPec
require 'config.php';
require '../src/core.php'; //PHPec framework main
//set global exception handler
set_exception_handler(function($err){
    die($err);
});

$app = new \PHPec\App();
//use middleware,you can use them one by one,or once by a array
/**
$app -> use([
	function($ctx){
		//do
		$ctx -> next();
		//do
	},
	'M1',
	'M2'
]);
*/

//Use Closure ;
$app -> use(function($ctx){
    $ctx -> text .= '[begin] ';
    $ctx -> next();// pass to next middleware,if not ,skip all fllow middleware include \PHPec\Router 
    $ctx -> text .= ' [end]';
    if(!$ctx -> body){
		$ctx -> body = $ctx -> text;
    }
});

//you can use a Object instance which implements \PHPec\Middleware
//so,you can use a Middleware with composer manage.
//$app -> use(new MyMiddleware()); //MyMiddleware must implements \PHPec\Middleware, 

//Use a function in file (search in "middleware" dir)
//function name as middleware name, filename is camel_case.php and then function name is CamelCase
$app -> use('M1'); //APP_PATH.'/middleware/m1.php';

//Use a class implement \PHPec\Middleware in file(middleware dir) like use function file
$app -> use('M2'); //APP_PATH.'/middleware/m2.php';

//skip other middleware,include \PHPec\Router
//$app -> use();

//this will not exec if $app -> use() effective
$app -> use(function($ctx){
   $ctx -> text .= ' [after $app->use()] ';
   $ctx -> next();
});


//if not use  $app -> use() to skip, \PHPec\Router will call
//i.e. 
//  /?c=User&a=profile  => call method profile @APP_PATH./controller/user.php
//  /?c=User&a=my       => call method _any @APP_PATH./controller/user.php (method "my" not found ,_any instead)
//  /?c=Shop&a=show     => call method show @APP_PATH./controller/any.php (shop.php not found,any.php instead)

$app -> run();

