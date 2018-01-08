PHPec开发框架
-------------

一个用极简的PHP WEB开发框架。

PHPec，即php easy, 这是多年前写过的一个MVC框架的名字。为了纪念，故继续取该名字，目标是做出一个易用、易学、易扩展的轻量开发框架。

**require**: PHP5.5+ || PHP7

> 本项目使用master分支作为开发分支，以release分支释出阶段可用版本。


## 特性说明

这是一个用php实现的模仿nodejs的koa经典的“洋葱模型”的WEB开发框架。

![flow](https://raw.githubusercontent.com/tim1020/PHPec/master/img/flow.png)

## 建议项目目录结构

```
confing.php //项目配置 
index.php   //项目入口
middleware/ //中间件目录
controller/ //控制器目录
libs/       //第三方库，包括PHPec
```

## 使用方法

### 入口

```
require __DIR__.'/config.php';
require __DIR__.'/libs/PHPec/core.php';

$app = new PHPec();
//加载中间件
$app->use('m1');
$app->use('m2');
$app->use(); //传递空参数时，所有后面的中间件被忽略，包括内置的Router;
$app->use('m3')

$app->run();
```

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
function M1($ctx){
	//do something
	$ctx->next();
	//do other
}
```

### 内置中间件

框架自带了常用的基本中间件，包括：

**ReqIo** : 最先进入的中间件，负责请求到达时对输入进行简单绑定，请求结束时，对响应内容进行输出。

	其它中间件或控制器，只需要对$ctx相应内容进行设置：

	$ctx->status  ：设置http响应码

	$ctx->body : 设置响应内容

**Router**: 路由中间件，最后进入的中间件。

如果你要使用自己的路由中间件，可使用$app->use();阻止进入内置路由。

```
$app->use('myRouter'); //或者使用函数方式，不调用$ctx->next从而不进入内置Router
$app->use();
$app->run();
```


### 命名空间

如果需要在middleware和controller中使用命名空间，需要将命名空间名称定义在 NS_MIDDLE和NS_CONTROL

```
//config.php
defined('NS_MIDDLE','middleware');

//m1.php
namespace middleware;
function M1($ctx){

}
```

### $ctx


### 内置路由说明

框架内置的路由中间件,实现了基本的路由功能。类型包括：
	 
	 1. query_string（c=resource&a=action） 

	 2. path_info (/resource/action) 

	 3. RESTful(Method /resource)  //请求方法作为acton，PATH支持两层资源定义，如：/shop/1/product/2

```
//继承\PHPec\BaseControl，实现_before或_after方法，会被控制器构造和析构时调用。
class Base extends \PHPec\BaseControl{
	function _before($ctx){
		//$ctx -> val = '_before';
	}
	function _after($ctx){
	}
}

//类名与文件名对应(类名用CamelCase，文件名用snake_case)
class User extends Base{
	function show($ctx){   //c=User&a=show 或 /User/show
	}
	function _any(){ //未定义action时，都命中_any
	}
}

//如果不需要_before和_after，也可以不继承\PHPec\BaseControll
//如果没有对应的文件，内置路由尝试查找Any.php
```