<?php
namespace PHPec;
defined('JWT_SECRET') || exit('JWT_SECRET not defined');

class Jwt implements Middleware{
    private $skip;
    private $hName   = 'Authorization';
    private $hPrefix = 'Bearer ';
    //指定不校验的条件，如果为null，则向后传递校验结果，否则校验失败直接返回
    //eg. $skip = ['header' => 'A=xxxxxx','pathinfo'=>''] //表示header头和值,只认一个，
    function __construct(Array $skip = null){
        $this -> skip = $skip;
    }
    //校验, 满足skip，通过, 否则验证一下，不满足就返回，满足就设置payload
    function begin($ctx){
        if($this -> skip ){
            $k = key($this -> skip);
            if(isset($ctx -> req -> header[$k]) && $ctx -> req -> header[$k] == $this -> skip[$k]) {
                return true;
            }
        }
        //需要验证
        $payload = false;
        if(isset($ctx -> req -> header[$this -> hName])){
            $token = str_replace($this -> hPrefix,"",$ctx -> req -> header[$this -> hName]);
            $payload = $this -> _verify($token);
        }
        if(false === $payload) {
            $ctx -> res('Unauthorized',401);
            return false;
        }
        $ctx -> reqPayload = $payload;
    }
    //生成
    function end($ctx){
        if(false != $ctx -> jwtPayload && is_array($ctx -> jwtPayload)){
            $header = ['typ'=>'JWT','alg'=>'HS256'];
            $claims = [
                'iss' => 'PHPec',
                'iat' => time(),
                'exp' => time() + 7200,
                'jti' => uniqid()
            ];
            $payload = array_merge($claims,$ctx -> jwtPayload);
            $str = sprintf("%s.%s",base64_encode(json_encode($header)),base64_encode(json_encode($payload)));
            $sign = hash_hmac('sha256', $str, md5( $str.JWT_SECRET ));
            //设置到header
            $ctx -> setHeader($this -> hName, $this -> hPrefix.$str.".".$sign);
        }
    }
    //验证，成功返回payload，失败返回false
    private function _verify($token){
        @list($header,$payload,$sign) = explode(".",$token);
        $str = $header.".".$payload;
        $expectSign = hash_hmac('sha256', $str,  md5( $str.JWT_SECRET));
        $header  = json_decode(base64_decode($header),true);
        $payload = json_decode(base64_decode($payload),true);
        if(!$header || !$payload || !$sign || $sign != $expectSign) return false;
        if(isset($payload['exp']) && $payload['exp'] < time()) return false; //expired
        return $payload;
    }
}