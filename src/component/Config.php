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
        $ks = explode(".", $k);
        $data = $this -> conf;

        foreach ($ks as $k) {
            if (empty($k)) {
                trigger_error('Config->get Fail: wrong key format', E_USER_ERROR);
            }
            if (isset($data[$k])) {
                $data = $data[$k];
            } else {
                return $default;
            }
        }
        return $data;
    }
}
