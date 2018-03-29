<?php
namespace PHPec\middleware;

if (!function_exists('getallheaders')) {
    function getallheaders()
    { 
        $headers = [];
        foreach ($_SERVER as $name => $value)  {
            if (substr($name, 0, 5) == 'HTTP_') {
                $k = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$k] = $value; 
            } 
        } 
        return $headers; 
    } 
} 
final class CommonIO implements \PHPec\interfaces\Middleware
{
    //Input handler
    public function begin($ctx)
    {
        $ctx -> method    = isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) :'GET';
        $ctx -> pathinfo  = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : null;
        $ctx -> query_str = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
        $ctx -> req = new \stdClass;
        $ctx -> req -> header = getallheaders();
        $ctx -> req -> get    = $_GET;
        $ctx -> req -> post   = $_POST;
        $ctx -> req -> cookie = $_COOKIE;
        //unset($_POST,$_GET,$_REQUEST,$_SERVER);
    }
    //Output handler
    public function end($ctx)
    {
        $code = [
            100 => "HTTP/1.1 100 Continue",
            101 => "HTTP/1.1 101 Switching Protocols",
            200 => "HTTP/1.1 200 OK",
            201 => "HTTP/1.1 201 Created",
            202 => "HTTP/1.1 202 Accepted",
            203 => "HTTP/1.1 203 Non-Authoritative Information",
            204 => "HTTP/1.1 204 No Content",
            205 => "HTTP/1.1 205 Reset Content",
            206 => "HTTP/1.1 206 Partial Content",
            300 => "HTTP/1.1 300 Multiple Choices",
            301 => "HTTP/1.1 301 Moved Permanently",
            302 => "HTTP/1.1 302 Found",
            303 => "HTTP/1.1 303 See Other",
            304 => "HTTP/1.1 304 Not Modified",
            305 => "HTTP/1.1 305 Use Proxy",
            307 => "HTTP/1.1 307 Temporary Redirect",
            400 => "HTTP/1.1 400 Bad Request",
            401 => "HTTP/1.1 401 Unauthorized",
            402 => "HTTP/1.1 402 Payment Required",
            403 => "HTTP/1.1 403 Forbidden",
            404 => "HTTP/1.1 404 Not Found",
            405 => "HTTP/1.1 405 Method Not Allowed",
            406 => "HTTP/1.1 406 Not Acceptable",
            407 => "HTTP/1.1 407 Proxy Authentication Required",
            408 => "HTTP/1.1 408 Request Time-out",
            409 => "HTTP/1.1 409 Conflict",
            410 => "HTTP/1.1 410 Gone",
            411 => "HTTP/1.1 411 Length Required",
            412 => "HTTP/1.1 412 Precondition Failed",
            413 => "HTTP/1.1 413 Request Entity Too Large",
            414 => "HTTP/1.1 414 Request-URI Too Large",
            415 => "HTTP/1.1 415 Unsupported Media Type",
            416 => "HTTP/1.1 416 Requested range not satisfiable",
            417 => "HTTP/1.1 417 Expectation Failed",
            500 => "HTTP/1.1 500 Internal Server Error",
            501 => "HTTP/1.1 501 Not Implemented",
            502 => "HTTP/1.1 502 Bad Gateway",
            503 => "HTTP/1.1 503 Service Unavailable",
            504 => "HTTP/1.1 504 Gateway Time-out",
            505 => "HTTP Version not supported"
        ];
        if (!isset($code[$ctx -> status])) {
            echo "Unknown http status code";
        } else {
            http_response_code($ctx -> status);
            $ctx -> setHeader('Content-Type', 'text/html;charset=utf-8');
            $contentType = 'text/html;charset=utf-8';
            if (null !== $ctx -> body) {
                if (is_array($ctx -> body) || is_object($ctx -> body)) {
                    $contentType = "application/json;charset=utf-8";
                    $ctx -> body = json_encode($ctx -> body);
                }
            } 
            if (empty($ctx -> resHeaders['Content-Type'])) {
                $ctx -> setHeader('Content-Type', $contentType);
            }
            if (count($ctx -> resHeaders) > 0) {
                if (headers_sent($f,$l)) {
                    trigger_error("Header already sent at $f line $l", E_USER_WARNING);
                }
                foreach ($ctx -> resHeaders as $k => $v) {
                    header("$k : $v");
                }
            }
            echo $ctx -> body;
        }
    }
}
?>
