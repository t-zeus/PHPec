<?php
namespace PHPec;
class BaseControl{
	function __construct($ctx){
		$this->ctx = $ctx;
		if(method_exists($this, '_before')){
			$this -> ctx -> logger -> debug('call _before');
			$this -> _before();
		}
	}
	function __destruct(){
		if(method_exists($this, '_after')){
			$this -> ctx -> logger -> debug('call _after');
			$this -> _after();
		}
	}
}
?>