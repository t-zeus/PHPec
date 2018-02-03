<?php
namespace PHPec;
//接口定义

//Middleware接口
interface Middleware{
    public function begin($ctx);
    public function end($ctx);
}
//LogWriter
interface LogWriter{
    public function write($msg,$type);
}