PHPec开发框架  [![License](https://img.shields.io/badge/license-MIT-blue.svg)](http://opensource.org/licenses/MIT)
-------------

一个极简的PHP WEB开发框架。

PHPec，即php easy, 这是多年前写过的一个MVC框架的名字。为了纪念，故继续取该名字，目标是做出一个易用、易学、易扩展的轻量开发框架。

**require**: PHP5.5+ || PHP7

> 本项目使用dev分支作为开发分支，阶段可用版本在master分支，并以版本号打tag。

## 特性说明

这是一个用php实现的模仿nodejs的koa经典的“洋葱模型”的WEB开发框架,核心代码非常的少，使用中间件模式，给开发者有足够的扩展自由度。

下面是其执行流程示意图

![flow](https://raw.githubusercontent.com/tim1020/PHPec/master/img/flow.png)

## 约定

phpec使用约定大于配置为主要原则

### 项目目录结构

框架会在**middleware**目录搜索中间件，在**controller**搜索控制器

```
confing.php //项目配置 
index.php   //项目入口
middleware/ //中间件目录
controller/ //控制器目录
logs/       //日志目录
vendor/     //第三方库，包括PHPec
```

### 常量及参数定义

APP_PATH:  开发者需在引入PHPec前定义项目的根目录，比如在入口文件中 define('APP_PATH',__DIR__);

用于$app -> use()或URI中的参数大小写敏感。

### 文件名与类名/函数名影射

框架在搜索中间件和控制器时，根椐给定的类名或方法名自动搜索相应的文件，规则是：

- 文件名使用snake_case.php格式，如: user_profile.php
- 类名/方法名使用CamelCase格式，如: UserProfile

如: $app -> use('MyRouter')，表示在APP_PATH.'/middleware/'下引入 my_router.php文件，并加载其中的名为MyRouter的类为函数

### Resource和action命名

resource和action，是路由中的path(/Resource/action)或querystring(?c=Resource&a=action)解析出来的内容，它们的命名规则是：

resource同时也是类名，以大写字母开头，只能由字母和数字组成
action同时是方法名，以小写字母开头，只能由字母和数字组成.

## 使用说明

可手工下载或使用composer下载本框架。框架example目录下有一个完整的简单使用例子，包括如何编写中间件及控制器。

> example同样作为unittest的mock程序，如果你更改了其中内容，可能会导致test fail。

### 入口

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
$app->use(['M1','M2']);
$app->use(); //传递空参数时，所有后面的中间件被忽略，包括内置的Router;
$app->use('M3')

$app->run();
```


### 中间件

phpec目前支持多种方式使用中间件，总有一款适合你。

1. 闭包函数

```
//使用Clouser时，直接在入口中使用
$app = new PHPec();
$app -> use(function($ctx){
	//do something
	$ctx -> next(); //函数方式需手动调起下一中间件,如果没有调用，则后面的中间件不会被执行。
	//do something;
});
```

2. 独立文件
 文件可以函数或实现了\PHPec\Middleware接口的类，文件名与类名/函数名对应，并保存在middleware目录

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

然后使用时用类名传入：
```
$app -> use('M1');
$app -> use('M2');
```

3. 类实例

类与独立文件类似，必须实现\PHPec\Middleware接口,但对文件名及保存位置没有要求，你甚至可以在一个文件中实现多个Middleware的类。

> 利用此特性，你甚至可以加载由composer管理的中间件库

```
require 'vendor/my/middle/My.php';
$app -> use(new MyMiddle()); //My.php中有Class MyMiddle implements \PHPec\Middleware
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

**需要注意的是：**

1. $tcx是全局生效的，意味着你在任一处设置$ctx的值后，在其它地方也可以通过$ctx参数获取或改写。

2. 在对$ctx赋值时，后面执行的赋值会覆盖前面的（以下划线开头的变量，只允许设置一次，重新设置时会报Warning，比如 ```$ctx -> _var1 = 123```）

3. 在中间件或路由的方法中，$ctx只是PHPec对象标识的指向，也即是说，你在路由或中间件中修改了$ctx本身（事实上你并没有理由这么做），并不会影响其它的中间件。

4. 数组只能一次设置（如果是对象，则可以先赋值后再设置）

```
$ctx -> ids = [1,2,3,4]; //ok
$ctx -> ids = [];
$ctx -> ids[0] = 1; // not ok
$ctx -> obj = new stdClass;
$ctx -> obj -> id = 12; //ok
...
```


### 内置组件

1. ReqIo : IO中间件

框架自动调度的的中间件，在所有自定义中间件之前被调度，负责请求到达时对输入进行简单绑定以及请求结束时，对响应内容进行输出。

对于输入，ReqIo暂时未作任何处理，因为PHP本身已对请求参数提供了方便操作的全局变量：$_GET,$_POST，$_COOKIES,$_SESSION 。

如有必要，可添加一个中间件对输入进行必要的安全性过滤，比如addslashes和xss过滤

对于输出处理，该中间件的只是简单的对$ctx->body内容进行输出（如果$ctx->body是数组或对象，则先json_encode，并使用content-type:application/json），应用开发者可添加其它中间件对$ctx->body进行过滤和转换，比如增加模板解释、多语言处理、api格式适配、jsonp输出等。

2. Router: 路由中间件

这是由框架自动调度的中间件，在所有开发者自定义的中间件的最后被调度，提供了路由到controller目录指定resource文件执行指定action的功能。

如果你需要使用自己的路由中间件，可使用$app->use();阻止进入内置路由。

```
$app->use('MyRouter'); //或者使用函数方式，不调用$ctx->next从而不进入内置Router
$app->use();
$app->run();
```

内置路由中间件，支持三种路由方式：

- 1 query_string（c=resource&a=action） 

- 2 path_info (/resource/action) 

- 3 RESTful(Method /resource)  //请求方法作为acton，PATH支持两层资源定义，如：/shop/1/product/2

> 如果需要使用path_info或RESTful,你可能需要配置一下nginx。

Router会根椐转换出来的resource和action，在项目的controller目录找到并加载resource对应的文件名，然后执行类中的action对应的方法。

如果资源文件不存在，则尝试用any.php文件和Any类替代，同样，如果类中不存在action对应的方法，也会尝试用_any方法替代

如果路由失败，会设置$ctx -> status = 404，并将$ctx -> body设置为失败的原因，如有必要可自行拦载并转换成需要的格式。

> 框架也会在Router后注入 $ctx -> resource,$ctx -> action, $ctx -> resId，目的是可以让开发者在内置路由之后处理一些无法路由的请求。


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

内置Router使用$ctx -> route_param中的参数(来自server参数)进行路由dispatch，也即时开发者有能力在路由之前修改以改变路由行为，以下是一个简单的路由别名处理例子。

```
//如果不作处理，默认 type=1，访问 /?c=User&a=Profile时会路由到 User -> profile方法
//更好面增加一个中间件改变这种行为，修改后，访问/?c=User&a=Profile会被重定向为/Shop/list
$app -> use(function($ctx){
    if($ctx -> route_param['qyery'] == 'c=User&a=Profile'){  //query实际是包括整个查询字符串的，这里只是简单演示
    	    //$ctx->router包括type,pathinfo,method,query，注意不要丢掉其它的属性
	    $router = $ctx -> route_param;
	    $router['type'] = 2;
	    $router['pathinfo'] = '/Shop/list';
	    $ctx -> router_param = $router;
    }
    //不要忘记交出控制权
    $ctx -> next();
});

$app -> run();
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
