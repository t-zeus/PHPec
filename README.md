PHPec开发框架  [![License](https://img.shields.io/badge/license-MIT-blue.svg)](http://opensource.org/licenses/MIT)
-------------

一个极简的PHP WEB开发框架。

PHPec，即php easy, 这是多年前写过的一个MVC框架的名字。为了纪念，故继续取该名字，目标是做出一个易用、易学、易扩展的轻量WEB开发框架。

**require**: PHP5.5+ || PHP7

> 本项目使用dev分支作为开发分支，阶段可用版本在master分支，并以版本号打tag。

## 特性说明

这是一个用php实现的模仿nodejs的koa经典的“洋葱模型”的WEB开发框架,核心代码非常的少，使用中间件模式，同时也提供了WEB开发必须的模块，比如路由和ORM，给开发者有足够的扩展自由度的同时也能开箱即用。

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


## 使用说明

可手工下载或使用composer下载本框架。框架example目录下有一个完整的简单使用例子，包括如何编写中间件及控制器。

> example同样作为unittest的mock程序，如果你更改了其中内容，可能会导致test fail。


```
//main index.php

require __DIR__.'/config.php';
require __DIR__.'/vendor/autoload.php'; //composer autoload
//require __DIR__.'/phpec/src/App.php'; //自行下载要引用src下的App.php

$app = new \PHPec\App();
//加载中间件
$app->use(function($ctx){
	//do something
	$ctx -> next();
	//do something
});
$app->use(['M1','M2']); //用数组传入多个中间件
$app->use(); //传递空参数时，所有后面的中间件被忽略，包括内置的Router;
$app->use('M3','param1'); //如果M3是一个Class，可以接受第二个参数作为其构造函数的参数

$app->run();
```


### 中间件

phpec目前支持多种方式使用中间件, 请参考 [如何编写及使用中间件](doc/middleware.md)

框架内置了WEB开发常用到的中间件:

1. [自动路由](doc/router.md)

2. [PDO ORM](doc/pdo_orm.md)


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

```

5. $ctx -> req

PHPec默认地将请求相关的内容绑定在$ctx -> req对象，包括 get,post,cookie,header，比如，可以用$ctx -> req -> post['user']来获得$_POST['user']

6. 提供 $ctx -> setHeader($k,$v) 设置响应的header

7. 提供 $ctx -> res($body,$status) 来设置输出内容和状态码

### logger

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

## License

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](http://opensource.org/licenses/MIT)
