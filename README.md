PHPec开发框架  [![License](https://img.shields.io/badge/license-MIT-blue.svg)](http://opensource.org/licenses/MIT)
-------------

一个极简的PHP WEB开发框架。

PHPec，即php easy, 这是多年前写过的一个MVC框架的名字。为了纪念，故继续取该名字，目标是做出一个易用、易学、易扩展的轻量开发框架。

**require**: PHP5.5+ || PHP7

> 本项目使用master分支作为开发分支，以release分支释出阶段可用版本。

## 特性说明

这是一个用php实现的模仿nodejs的koa经典的“洋葱模型”的WEB开发框架。

下面是其执行流程示意图

![flow](https://raw.githubusercontent.com/tim1020/PHPec/master/img/flow.png)


## 建议项目目录结构

```
confing.php //项目配置 
index.php   //项目入口
middleware/ //中间件目录
controller/ //控制器目录
logs/       //日志目录
vendor/     //第三方库，包括PHPec
```

## 基本用法


```
//main index.php

require __DIR__.'/config.php';
require __DIR__.'/vendor/PHPec/core.php';

$app = new PHPec();
//加载中间件
$app->use(function($ctx){
	//do something
	$ctx -> next();
	//do something
});
$app->use('m2');
$app->use(); //传递空参数时，所有后面的中间件被忽略，包括内置的Router;
$app->use('m3')

$app->run();
```

使用方法可参考example目录下的例子


### 中间件

phpec目前支持三种方式使用中间件，包括闭包函数，独立函数，实现\PHPec\Middleware接口的类。

```
//使用Clouser时，直接在入口中使用
$app = new PHPec();
$app -> use(function($ctx){
	//do something
	$ctx -> next(); //函数方式需手动调起下一中间件,如果没有调用，则后面的中间件不会被执行。
	//do something;
});
```

使用类或非闭包函数作为中间件时，需将中间件以文件保存在middleware目录，并使文件名与类名（或函数名对应）

文件名使用snake_case，类名使用CamelCase，如：类ErrorHanlder对应文件error_hanlder.php


```
//使用实现\PHPec\Middleware接口的类，需实现begin($ctx)和end($ctx)方法
//该方式无需手动调用$ctx->next(),在执行完begin方法后，框架自动调度next方法
//m1.php
class M1 implements \PHPec\Middleware {
	function begin($ctx){
		$ctx -> body = 'hello';
	}
	function end($ctx){
	}
}
```


```
//独立函数与闭包类似，需手动调用next
//m1.php
function M1($ctx){
	//do something
	$ctx->next();
	//do other
}
```

### 命名空间

如果需要在middleware和controller中使用命名空间，需要将命名空间名称定义为 NS_MIDDLE和NS_CONTROL常量

```
//config.php
defined('NS_MIDDLE','middleware');

//m1.php
namespace middleware;
function M1($ctx){

}
```

### $ctx

框架使用PHPec对象本身作为$ctx，并使用魔术函数来设置和读取PHPec未定义属性，开发者可以在中间件中使用 $ctx -> xxx来设置或读取，如：

$ctx -> status = 404         //设置http response code为404

$ctx -> body = 'hello world' //设置response的body内容为hello world

你还可以用$ctx来访问其它自定义的属性，或绑定一些方法。

$ctx -> myVar = 'xxxx';

$ctx -> myObj = new XX();

**特别注意：** $tcx是全局生效的，意味着你在任一处设置$ctx的值后，在其它地方也可以通过$ctx参数获取，所以，在对$ctx赋值时要注意（因为后面的赋值会覆盖前面的），特别是不能直接赋值给$ctx，比如 $ctx = 'xxx'，这会使应用发生不可预期的错误。

### 内置组件

1. ReqIo : IO中间件

框架自动调度的的中间件，在所有自定义中间件之前被调度，负责请求到达时对输入进行简单绑定以及请求结束时，对响应内容进行输出。

对于输入，ReqIo暂时未作任何处理，因为PHP本身已对请求参数提供了全局变量：$_GET,$_POST，$_COOKIES,$_SESSION 。

如有必要，可添加一个中间件对输入进行必要的安全性过滤，比如addslashes

对于输出处理，该中间件的只是简单的对$ctx->body内容进行输出（如果$ctx->body是数组或对象，则先json_encode，并使用content-type:application/json），应用开发者可添加其它中间件对$ctx->body进行过滤和转换，比如增加模板解释、多语言处理、api格式适配等。

2. Router: 路由中间件

这是由框架自动调度的中间件，在所有开发者自定义的中间件的最后被调度，提供了路由到controller目录指定resource文件执行指定action的功能。

如果你需要使用自己的路由中间件，可使用$app->use();阻止进入内置路由。

```
$app->use('myRouter'); //或者使用函数方式，不调用$ctx->next从而不进入内置Router
$app->use();
$app->run();
```

内置路由中间件，支持三种路由方式（大小写敏感）：

- query_string（c=resource&a=action） 

- path_info (/resource/action) 

- RESTful(Method /resource)  //请求方法作为acton，PATH支持两层资源定义，如：/shop/1/product/2

> 如果需要使用path_info或RESTful,你可能需要配置一下nginx。

Router会根椐转换出来的resource和action，在项目的controller目录找到并加载resource对应的文件名，然后执行类中的action对应的方法。

如果资源文件不存在，则尝试用any.php替代，同样，如果类中不存在action对应的方法，也会尝试用_any方法替代

如果路由失败，会设置$ctx -> status = 404，并将$ctx -> body设置为失败的原因，如有必要可自行拦载并转换成需要的格式。


内置的路由最终的路由目标是某个resource的action，在这里，控制器与resource定义一致。

内置路由对控制器的唯一要求是action的原型需接受$ctx参数，如果你需要在控制器执行之前或之后都执行指定的处理，可继承\PHPec\BaseControl 并实现_before（$ctx）和_after($ctx)方法。

```
//继承\PHPec\BaseControl，实现_before或_after方法，会被控制器构造和析构时调用。
class Base extends \PHPec\BaseControl{
	function _before($ctx){
		//$ctx -> val = '_before';
	}
	function _after($ctx){
	}
}

//类名与文件名对应(类名用CamelCase，文件名用snake_case.php)
//user.php
class User extends Base{
	function show($ctx){   //c=User&a=show 或 /User/show
	}
	function _any(){ //未定义action时，都命中_any
	}
}
```

3. logger类

PHPec内置了一个logger类，并绑定在$ctx -> logger中，该类提供几个输出日志的方法：

```
$ctx -> logger -> debug();
$ctx -> logger -> info();
$ctx -> logger -> warn();
$ctx -> logger -> error();
```
$ctx->logger是一个可扩展的实现，内置的实现，会将日志以“类型_yyyymmdd.log”为文件名保存在logs目录。哪些日志要输出，使用常量LOG_LEVEL来定义

debug(1), info(2),warn(4),error(8)，比如： LOG_LEVEL = 15表示全部(1+2+4+8)

如果你想改变$ctx->logger的输出方式，可以在new PHPec()时传入一个实现了\PHPec\LogWriter接口的writer,如：

```
class MyLogWriter implements \PHPec\LogWriter{
	//$msg为日志内容，$type为类型(debug,info,warn,error)
	function write($msg,$type){
		//具体处理，比如发到消息队列
	}
}
$app = new PHPec(new MyLogWriter());
```




如果不想改变框架内置的logger行为，同样可以使用\PHPec\Logger自己创建一个logger对象

```
$logger = new \PHPec\Logger(); //不传参表示使用内置writer，你可以指定自己的writer
```

## Other

Tim &lt;tim8670@gmail.com&gt; 基于[![License](https://img.shields.io/badge/license-MIT-blue.svg)](http://opensource.org/licenses/MIT)
