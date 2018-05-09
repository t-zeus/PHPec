<?php
namespace PHPec\component;

class Validator
{
    /**
     * 使用规则$rule对数据$data进行校验，如果一切正常，返回true, 否则返回错误数组
     * $rule = ['k1' => 'rule1|rule:param1,param2']
     * 使用 | 连接多个规则，需同时满足，每条规则是： [规则名：参数，参数], 可以没有附加参数
     * 可用规则 required,date,timestamp,alpha,alpha_num,
     *         numeric,int,int_between,len_between,
     *         regex,bool,date_between,timestamp_between
     */
    public function check($data, $rules)
    {
        $err = [];
        foreach ($rules as $k => $rule) {
            $r = explode("|", $rule); // [rule,rule1:p1,rule2:p1,p2]
            foreach ($r as $v) {
                @list ($m, $p) = explode(":", $v);
                $method = $this -> _rule2Method($m);
                if ($m == 'required') {
                    if (empty($data[$k])) {
                        $err[$k] = $m;
                        break 1;
                    }
                    continue;
                }
                if (!method_exists($this, $method)) trigger_error("Validator rule error: $method,   $k  => $rule invalid", E_USER_ERROR);
                if (!empty($data[$k])) {
                    $params = [$data[$k]];
                    if (!empty($p)) {
                        @list($min, $max) = explode(",", $p);
                    }
                    $params[] = empty($min) ? null : $min;
                    $params[] = empty($max) ? null : $max;
                    $result = call_user_func(array($this, $method), ...$params);
                    if (!$result) {
                        $err[$k] = $m;
                        break 1;
                    }
                }
            } 
        }
        return empty($err) ? true : $err; 
    }
    //转换规则名至方法名
    private function _rule2Method($rule)
    {
        $str = ucwords(str_replace('_', ' ', $rule));
        $str = str_replace(' ','',lcfirst($str));
        return "check".ucfirst($str);
    }

    //合法的日期格式
    protected function checkDate($val)
    {
        return false != strtotime($val);
    }
    //合法的时间戳
    protected function checkTimestamp()
    {
        return (strtotime(date('m-d-Y H:i:s',$timestamp)) === $timestamp);
    }
    //字母
    protected function checkAlpha($val)
    {
        return preg_match('/^[a-z]*$/i', $val);
    }
    //数字和字母
    protected function checkAlphaNum($val)
    {
        return preg_match('/^[a-z0-9]*$/i', $val);
    }
    //有效数字
    protected function checkNumeric($val)
    {
        return is_numeric($val);
    }
    //整数
    protected function checkInt($val)
    {
        return(ctype_digit(strval($val)));
    }
    //整数值范围
    protected function checkIntBetween($val, $min, $max)
    {
        if (! $this -> checkInt($val)) return false;
        if ($min != null) {
            if (!$this -> checkInt($min)) trigger_error("Validator rule error: int_between:$min,$max", E_USER_ERROR);
            if ($min > $val) return false;
        }
        if ($max != null) {
            if (!$this -> checkInt($max)) trigger_error("Validator rule error: int_between:$min,$max", E_USER_ERROR);
            if ($max < $val) return false;
        }
        return true;
    }
    //字符长度范围
    protected function checkLenBetween($val, $min, $max)
    {
        $len = mb_strlen($val, 'utf8');
        if ($min != null) {
            if (!$this -> checkInt($min)) trigger_error("Validator rule error: int_between:$min,$max", E_USER_ERROR);
            if ($min > $len) return false;
        }
        if ($max != null) {
            if (!$this -> checkInt($max)) trigger_error("Validator rule error: int_between:$min,$max", E_USER_ERROR);
            if ($max < $len) return false;
        }
        return true;
    }
    //日期范围
    protected function checkDateBetween($val, $min, $max)
    {
        if (!$this -> checkDate($val)) return false;
        $val = strtotime($val);
        if ($min != null) {
            if (!$this -> checkDate($min)) return trigger_error("Validator rule error: date_between: $min, $max", E_USER_ERROR);
            if (strtotime($min) > $val) return false;
        }
        if ($max != null) {
            if (!$this -> checkDate($max)) return trigger_error("Validator rule error: date_between: $min, $max", E_USER_ERROR);
            if (strtotime($max) < $val) return false;
        }
        return true;
    }
    //时间戳范围
    protected function checkTimestampBetween($val, $min, $max)
    {
        if (!$this -> checkTimestamp($val)) return false;
        if ($min != null) {
            if (!$this -> checkTimestamp($min)) return trigger_error("Validator rule error: timestamp_between: $min, $max", E_USER_ERROR);
            if ($min > $val) return false;
        }
        if ($max != null) {
            if (!$this -> checkTimestamp($max)) return trigger_error("Validator rule error: timestamp_between: $min, $max", E_USER_ERROR);
            if ($max < $val) return false;
        }
        return true;
    }
    //布尔
    protected function checkBool($val)
    {
        return is_bool($val);
    }
    //正则校验
    protected function checkRegex($val, $pattern)
    {
        return preg_match($pattern, $val);
    }
}