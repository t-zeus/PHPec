<?php
//use class ,$ctx->next() will auto call
class M2 implements \PHPec\Middleware{
    //call before next
    function begin($ctx){
		$ctx -> text.= '>m2';
    }
    //call after next
    function end($ctx){
		$ctx -> text .= '>m2 end';
    }
}
