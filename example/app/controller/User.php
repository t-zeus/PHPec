<?php
namespace example\controller;

class User {
    //for index.php?c=User&a=show
    function show($ctx){
        $ctx -> body = ['name' => 'Tim'];
    }
    //for index.php?c=User
    function _any($ctx){
        $ctx -> body = 'hello User';
    }
}
