<?php
namespace PHPec;

class BaseControl
{
    public $halt = false;
    function __construct($ctx)
    {
        $this->ctx = $ctx;
        if (method_exists($this, '_before')) {
            $this -> _before($this->ctx);
        }
    }
    function __destruct()
    {
        if (method_exists($this, '_after')) {
            $this -> _after($this->ctx);
        }
    }
}