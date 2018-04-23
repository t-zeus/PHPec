<?php
namespace PHPec\middleware;

/**
 * 其于Session的登录认证
 * 
 * @depends Config, Auth, Session
 */
final class SessionAuth implements \PHPec\interfaces\Middleware
{
    use \PHPec\DITrait;

    public function enter($ctx)
    {
        if (!$this -> Session -> get('user')) {  //未认证通过
            if ($this -> Session -> get('authorization')) {
                if (empty($ctx -> _P['account']) || empty($ctx -> _P['password'])) {
                    $errorMsg = '请输入用户名密码登录';
                } else {
                    $result = $this -> Auth -> verify($ctx -> _P['account'], $ctx -> _P['password']);
                    if (false !== $result) { //密码验证通过
                        $this -> Session -> delete('authorization');
                        $this -> Session -> set('user', $result);
                    } else {
                        $errorMsg = '用户名或密码错';
                    }
                }
            } else {
                $this -> Session -> set('authorization', true);
                $errorMsg = "未登录";
            }

            if (empty($errorMsg)) {
                $ctx -> body = ['result' => 'ok'];
            } else {
                $ctx -> body = ['result' => 'Unauthorized','error' => $errorMsg];
            }
            return false;
        }
    }
    public function leave($ctx)
    {

    }
}