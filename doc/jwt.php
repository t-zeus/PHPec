Jwt
------

JWT是一种简单的API认证方式，具体介绍请自行了解。

phpec内置了jwt的生成和验证，如要使用，请使用 ```$app -> use('\PHPec\Jwt',$skip)```引入。

## 校验

引入后，phpec会自动对请求进行jwt的校验。

对于不需要校验的接口（比如登录验证的请求），可以通过$skip声明对带有指定的header来跳过。

```
$skip = ['X-SKIP' => "DON'T VERIFY"]; //当请求头带有X-SKIP且值为“DON'T VERIFY”时不校验
$app -> use('\PHPec\Jwt',$skip);
```

> 如果认证失败，JWT中间件直接响应401，如果成功，且将payload内容绑定到$ctx -> payload，后续可以通过该值来判断是否已认证。

> $skip一般只是用来跳过登录请求的校验，后续的逻辑应该对没有$ctx -> payload的请求进行二次判断（即只对提供登录请求进行处理，其它请求认为是非法）


## 生成JWT

开发者可以通过设置 $ctx -> jwtPayload 来通知中间件处理。格式如下：

```
$ctx -> jwtPayload = [
	'sub' => 'myApp',
	'uid' => 12345
];

```

只需要设置自已的claims即可，中间件已默认设置了部分reserved claims。如果设置 $ctx -> jwtPayload时指定了reserved claims，则会覆盖默认的设置。


中间件会生成JWT，并通过header('Authorization','Bearer:xxxxxx') 来响应。请求时需按原样带上。