<?php
namespace PHPec\component;

/**
 * session存取封装 
 */
class Session
{
    use \PHPec\DITrait;
    /**
     * 注册sessionHandler,执行session_start();
     */
    public function __construct()
    {
        $handler = $this -> Config -> get('session.handler','');
        if (!empty($handler)) {
            if (! $this -> $handler instanceof \SessionHandlerInterface) {
                trigger_error("Invalid SessionHandler : $handler", E_USER_ERROR);
            }
            session_set_save_handler($this -> $handler);
        }
        session_start();//该方法会依赖cookie中读取PHPSESSID
    }

    
    public function get($key) 
    {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }
    public function set($key, $val)
    {
        $_SESSION[$key] = $val;
    }
    public function delete($key)
    {
        unset($_SESSION[$key]);
    }
    public function getAll(){
        return $_SESSION;
    }
   
    //提交并结束会话
    public function commit()
    {
        return session_write_close();
    }
}