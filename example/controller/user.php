<?php
class User{
    function profile($ctx){
	$ctx -> body = "User->profile";
    }
    function _any($ctx){
	$ctx -> body = "User->_any";
    }
}
