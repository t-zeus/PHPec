如果编写及使用中间件
--------------------


## 使用

在phpec中要调用中间件，使用$app -> use()方法，该方法可以接受多种方式的参数（如果use方法参数为空或者false，表示跳过后续的中间件，包括内置的router.）

1. 闭包函数

```
//使用Clouser时，直接在入口中使用
$app = new \PHPec\App();
$app -> use(function($ctx){
    //do something
    $ctx -> next(); //函数方式需手动调起下一中间件,如果没有调用，则后面的中间件不会被执行。
    //do something;
});
```

2. 独立文件

 文件可以是函数或实现了\PHPec\Middleware接口的类，文件名与类名/函数名对应，并保存在middleware目录

```
//使用实现\PHPec\Middleware接口的类，需实现begin($ctx)和end($ctx)方法
//该方式无需手动调用$ctx->next(),在执行完begin方法后，框架自动调度next方法
//m1.php
class M1 implements \PHPec\Middleware {
    function __construct($param = null){
    }
    function begin($ctx){
        $ctx -> body = 'hello';
    }
    function end($ctx){
    }
}
```

```
//独立函数与闭包函数类似，需手动调用next
//m1.php
function M1($ctx){
    //do something
    $ctx->next();
    //do other
}
```

然后在使用时，用类名或函数名传入：
```
$app -> use('M1');
$app -> use('M2','param1'); //如果是类的方式，可使用第二参数为类构造函数的参数
```

3. 类实例

类实例必须实现\PHPec\Middleware接口,同时需new生成实例后再传入，所以对文件名及保存位置没有要求，你甚至可以在一个文件中实现多个Middleware的类。

> 利用此特性，你甚至可以加载由composer管理的中间件库，实例化时也可以向构造函数传入更多的参数。

```
require 'vendor/my/middle/My.php'; //或者使用composer的autoload
$app -> use(new MyMiddle('paam1')); //My.php中有Class MyMiddle implements \PHPec\Middleware
```

4. 通过函数返回

即再定义一个函数，通过不同的参数返回不同的中间件，包括闭包，类实例等。
```
function myMiddle($p){
    switch($p){
        case 'A':
        return function($ctx){
            //
        };
        case 'B':
        return 'M1';
    }
}
$app -> use(myMiddle('A'));
```

5. 通过数组一次传入多个中间件

```
$app -> use(['M1','M2',function($ctx){},'M3']); 
//数组方式要跳过后面的中间件时，可使用空字符串或者null
$app -> use(['M1','M2',null);
```


## 内置中间件

框架内容了常用的中间件

1. ReqIo : IO中间件

框架自动调度的的中间件，在所有自定义中间件之前被调度，负责请求到达时对输入进行简单绑定以及请求结束时，对响应内容进行输出。

ReqIo只对请求内容绑定到$ctx -> req,并没有做其它任何处理，在使用phpec进行项目开发时，建议添加一个中间件对输入进行必要的安全性过滤，比如addslashes和xss过滤

对于输出处理，该中间件的只是简单的对$ctx->body内容进行输出（如果$ctx->body是数组或对象，则先json_encode，并使用content-type:application/json），应用开发者可添加其它中间件对$ctx->body进行过滤和转换，比如增加模板解释、多语言处理、api格式适配、jsonp输出等。

2. Router : 自动路由中间件

这是由框架自动调度的中间件，在所有开发者自定义的中间件的最后被调度，提供了路由到controller目录指定resource文件执行指定action的功能。

如果你需要使用自己的路由中间件，可使用$app->use();阻止进入内置路由。

请参考 [自动路由中间件](router.md)

3. PdoOrm: pdo orm操作

该中间件不会自动调用，如果需要使用，可以自行引入:

```
$app -> use('\PHPec\PdoOrm');
```

请参考 [PdoOrm中间件](pdo_orm.md)

4. MongoOrm: mongo orm操作

该中间件不会自动调用，如果需要使用，可以自行引入:

```
$app -> use('\PHPec\MongoOrm');
```

请参考 [MongoOrm中间件](mongo_orm.md)

4. Logger

该中间件不会自动调用，如果需要使用，可以自行引入:

```
$app -> use('\PHPec\Logger');
```

请参考 [Logger](logger.md)