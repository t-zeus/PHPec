<?php
//辅助函数,定义一些通用处理
defined('APP_PATH')  || exit('APP_PATH not defined');
defined('APP_NS')  || define('APP_NS', '');

//hander E_USER_ERROR
set_error_handler(function($errno, $errstr, $errfile, $errline){
    if ($errno == E_USER_ERROR) {
        throw new \Exception($errstr, 1);
    } elseif ($errno == E_USER_WARNING) {
        echo "PHPec Warning: ".$errstr."\n\n";
        //todo: log
    }
    return false;
});

//autoload
spl_autoload_register(function($className){
    $pieces = explode('\\', $className);
    $class  = array_pop($pieces);
    if (preg_match('/^[A-Z]{1}[A-Z\/a-z\d]*$/', $class) === 0) {
        trigger_error("className '$class' not a CamelCase format", E_USER_ERROR);
    }
    array_shift($pieces);
    $path = str_replace('.', '', implode("/", $pieces)); //trim . for safe;
    if (strpos($className, 'PHPec\\') === 0) { //框架类
        $mFile   = sprintf('%s/%s/%s.php', __DIR__ , $path, $class);
    } else {
         $mFile   = sprintf('%s/%s/%s.php', APP_PATH , $path, $class);
    }

    if (file_exists($mFile)) {
        require_once $mFile;
    } else {
        trigger_error("autoload {$className} fail -- file not found", E_USER_ERROR);
    }
});
?>