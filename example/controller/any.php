<?php
//extends \PHPec\BaseControl,will auto call _before and _after
class Any extends \PHPec\BaseControl {
    function _before($ctx){
         $ctx -> body = "[before]";
    }
    function _after($ctx){
        $ctx -> body.="[after]";
    }
    function show($ctx){
	$ctx -> body.="Any->show";
    }
    function get($ctx){
	$ctx -> body.="Any->get";
    }
    function _any($ctx){
	$ctx -> body.= 'Any->_any';
    }
} 
