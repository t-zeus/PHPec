<?php
namespace PHPec\middleware;

class ViewRender implements \PHPec\interfaces\Middleware
{
    public function begin($ctx)
    {
    }
    public function end($ctx)
    {
        if (!empty($ctx -> body) && !empty($ctx -> template)) {
            $tpl = str_replace('.', '', $ctx -> template);
            $tplFile = APP_PATH.'/view/'.$tpl.'.tpl';
            if (!file_exists($tplFile)) {
                trigger_error("template file not found", E_USER_ERROR);
            }
            require $tplFile;
            $ctx -> body = '';
        }
    }
}