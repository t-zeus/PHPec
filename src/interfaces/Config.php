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
     * @param string $k 
     * @param mixed $default set default value if $k not found
     * @return mixed    config value
     */
    public function get($k, $default = null);
}