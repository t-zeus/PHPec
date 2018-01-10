<?php
// if use namespace,define NS_MIDDLE = namespace name
function M1($ctx){
    $ctx -> text.=">m1";
    $ctx -> next();//if not this,other middleware fllow this will skip
    $ctx -> text.='>m1 end';
}
