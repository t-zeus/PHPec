<?php
namespace PHPec\middleware;

/**
 * Json Web Token处理
 * 
 * @depends Config, User
 */
final class JWT implements \PHPec\interfaces\Middleware
{
     use \PHPec\DITrait;

     private $secret = '';

    /**
      * 验证： 有token,验证token，如果token为空，则验证用户名密码,通过后生成token
      */
    public function enter($ctx)
    {
        $useCookie = $this -> Config -> get('jwt.use_cookie',false); //是否使用Cookie传输cookieS
        $this -> secret = $this -> Config -> get('jwt.secret');
        if (empty($this -> secret)) { //抛错
            trigger_error("config jwt.secret not set", E_USER_ERROR);
        }
        if (!$useCookie) { //默认不使用COOKIE传输token，可在配置中设置为cookie方式
            $token = empty($ctx -> req -> header['Authorization']) ? '' : $ctx -> req -> header['Authorization'];   
        } else {
            $token = empty($ctx -> req -> cookie['Authorization']) ? '' : $ctx -> req -> cookie['Authorization'];
        }
        if (!empty($token)) { //verify token
            $result = $this -> _verify($token);
            if (false === $result) { 
                $ctx -> res('Unauthorized',401);
                return false;
            }
            $ctx -> jwtPayload = $result;
        } else { //token为空，用户密码认证
            if (empty($ctx -> req -> post['account']) || empty($ctx -> req -> post['password'])) {
                $ctx -> res('Unauthorized',401);
            } else {
                //todo: 验证码防止多次重试
                $result = $this -> Auth -> verify($ctx -> req -> post['account'], $ctx -> req -> post['password']);
                if (false === $result) { //密码验证失败
                    $ctx -> body = '登录失败';
                } else {
                    $token = $this -> _buildToken($result);
                    if ($useCookie) {
                        setcookie('Authorization', $token, time() + $this -> Config -> get('jwt.exp_time', 7200));
                    } else {
                        $ctx -> setHeader('Authorization', 'Bearer '.$token);
                    }
                }
            }
            return false; //skip other middleware
        }
    }

    //生成
    private function _buildToken(Array $payload)
    {
        $exp = $this -> Config -> get('jwt.exp_time', 7200);
        $header = ['typ'=>'JWT','alg'=>'HS256'];
        $claims = [
            'iss' => 'PHPec',
            'iat' => time(),
            'exp' => time() + $exp,
            'jti' => uniqid()
        ];
        $payload = array_merge($claims, $payload);
        $str = sprintf("%s.%s",base64_encode(json_encode($header)),base64_encode(json_encode($payload)));
        $sign = hash_hmac('sha256', $str, md5( $str. $this -> secret));
        return $str.".".$sign;   
    }
    //验证，成功返回payload，失败返回false
    private function _verify($token)
    {
        @list($header,$payload,$sign) = explode(".", $token);
        $str = $header.".".$payload;
        $expectSign = hash_hmac('sha256', $str,  md5( $str.$this -> secret));
        $header  = json_decode(base64_decode($header),true);
        $payload = json_decode(base64_decode($payload),true);
        if (!$header || !$payload || !$sign || $sign != $expectSign) return false;
        if (!isset($payload['exp']) || $payload['exp'] < time()) return false; //expired
        return $payload;
    }

    public function leave($ctx)
    {
    }
}