<?php
namespace example\controller;

class User {
    function show($ctx){
        $ctx -> body = ['name' => 'Tim'];
    }
    function _any($ctx){
        $ctx -> body = 'hello User';
    }
}
