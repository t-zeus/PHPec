<?php
//use class ,$ctx->next() will auto call
class M3 implements \PHPec\Middleware{
	function __construct($arg){
		$this -> arg = $arg;
	}
    //call before next
    function begin($ctx){
		$ctx -> text.= $this -> arg;
    }
    //call after next
    function end($ctx){
		$ctx -> text .= '>m3 end';
    }
}
