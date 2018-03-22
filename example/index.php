<?php
//example for PHPec
define('APP_PATH', __DIR__.'/app');
define('APP_NS', 'example');
date_default_timezone_set('UTC');

require '../../../autoload.php'; //composer autoload


try {
    $app = new \PHPec\App();

    //Use Closure ;
    $app -> use(function($ctx){
        $ctx -> text .= '[begin] ';
        $ctx -> next();// pass to next middleware,if not ,skip all fllow middleware include \PHPec\Router 
        $ctx -> text .= ' [end]';
        if(!$ctx -> body){
    		$ctx -> body = $ctx -> text;
        }
    });

    //Use a class implement \PHPec\interfaces\middleware in file(middleware dir) 
    $app -> use('M1'); //APP_PATH.'/middleware/M1.php';

    //skip other middleware,include \PHPec\middleware\Router
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
} catch (Exception $e) {
    var_dump($e);
}

