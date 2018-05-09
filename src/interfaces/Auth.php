<?php
namespace PHPec\interfaces;

/**
 * 登录授权的帐号密码验证
 */
interface Auth
{
    /**
     * verify account/password
     * 
     * @param string $account 
     * @param string $password
     * @return mixed false or array payload
     */
    public function verify($account, $password);
}