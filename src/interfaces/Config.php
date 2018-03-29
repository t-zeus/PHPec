<?php
namespace PHPec\interfaces;

/**
 * get a config field from config file or other
 */
interface Config
{
    /**
     * get a config field
     * 
     * @param  String $k 
     * @return mixed    config value
     */
    public function get($k, $default = null);
}