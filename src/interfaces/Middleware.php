<?php
namespace PHPec\interfaces;

/**
 * PHPec Middleware interface
 */
interface Middleware
{   
    /**
     * action of middleware in
     * @param  Object $ctx PHPec app
     * @return bool     don't yield next if return false 
     */
    public function enter($ctx);
     /**
     * action of middleware exit
     * @param  Object $ctx PHPec app
     * @return none
     */
    public function leave($ctx);
}