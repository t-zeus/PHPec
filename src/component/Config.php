<?php
namespace PHPec\component;

class Config implements \PHPec\interfaces\Config
{
    private $conf = [];
    function __construct()
    {
        $mConfig = APP_PATH.'/config/app.php';
        if (file_exists($mConfig)) $this -> conf = require $mConfig;
    }
    function get($k, $default = null)
    {
        return isset($this -> conf[$k]) ? $this -> conf[$k] : $default;
    }
}
