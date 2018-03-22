PHPec开发框架  [![License](https://img.shields.io/badge/license-MIT-blue.svg)](http://opensource.org/licenses/MIT)
-------------

一个极简的PHP WEB开发框架。 [点此查看详细手册](doc/manual.md)

PHPec，读作php easy, 这是多年前写过的一个MVC框架的名字。为了纪念，故继续取该名字，目标是做出一个易用、易学、易扩展的轻量WEB开发框架。

**require**: PHP5.5+ || PHP7

> 本项目使用dev分支作为开发分支，阶段可用版本在master分支，并以版本号打tag。


## 特性

- 仿koa经典的“洋葱模型”中间件模式。

下面是其执行流程示意图

![flow](https://raw.githubusercontent.com/tim1020/PHPec/master/doc/flow.png)

- 内置自动规则路由，支持QUERY_STRING,PATHINFO及RESTFUL方式

- 一般组件支持自动依赖注入

- 提供WEB开发基本模块(逐步添加完善)


### 中间件

中间件为请求处理的必经路径，通常负责处理输入输出、权限认证等。

### 一般组件

一般组件定义为实现一个功能的普通类，可以在需要时被自动注入，常用于逻辑处理、数据处理等。

### $ctx

$ctx为贯穿整个请求流程的上下文对象，即App对象本身。开发者可以在中间件或控制器方法中使用$ctx来读取或设置一些属性。

## 开始使用

可手工下载或使用composer下载本框架。框架example目录下有一个完整的简单使用例子，包括如何编写中间件及控制器。

> example同样作为unittest的mock程序，如果你更改了其中内容，可能会导致test fail。


```
//main index.php
define('APP_PATH', __DIR__.'/app');
define('APP_NS', 'myapp');  //项目根命名空间

require __DIR__.'/vendor/autoload.php'; //composer autoload
//require __DIR__.'/phpec/src/App.php'; //自行下载要引用src下的App.php

$app = new \PHPec\App();
//加载中间件
$app->use(function($ctx){
    $ctx -> body = 'hello';
    $ctx -> next();
    $ctx -> body .= ' phpec';
});
//$app->use(['M1','M2']); //用数组传入多个中间件
$app->use(); //传递空参数时，所有后面的中间件被忽略，包括内置的Router;
//$app->use('M3','param1'); //如果M3是一个Class，可以接受第二个参数作为其构造函数的参数

$app->run();
```

## 约定

PHPec使用约定大于配置为原则，在使用时，需注意遵守。

### 项目目录结构

```
APP_SRC/
    app/                //应用代码
        config/             //配置文件目录
        controller/         //控制器目录
        middleware/         //中间件目录
        service/            //service class目录，默认被自动注入的查找目录
        model/              //数据模型
        interfaces/         //接口定义
    runtime/            //运行时存储目录，包括log和cache，需可以权限
        cache/
        log/
    vendor/             //composer安装的库，包括PHPec
    readme.md 
    composer.json 
    index.php           //主入口，你也可以将此文件放进public目录
```

### 常量

APP_PATH:  开发者需在引入PHPec前定义项目的根目录(指向项目代码的app目录)，比如在入口文件中 define('APP_PATH', \__DIR\__.'/app');

APP_NS:  项目根命名空间

### 命名空间

1. 使用APP_NS常量定义项目根命名空间，如 ```define('APP_NS', 'myapp')```

2. 控制器、中间件、service的命名空间固定为其目录名，可以在目录中添加多层目录来表示多层的命名空间。

```
//注意要加上前缀

//controller命名空间
namespace myapp\controller;

//controller目录的user目录下的控制器类的命名空间
namespace myapp\controller\user;

//middleware命名空间
namespace myapp\middleware;

//service命名空间
namespace myapp\service;
```

### 配置文件 

- 配置文件保存在app/config目录

- 使用app.php作为主配置文件，如有多个配置，需将其它配置都读入到app.php进行合并

- 配置文件使用 ```return 数组```来返回配置

```
//app.php
return [
    'log_path' => '/tmp';
];
```

### 文件名与类名

文件名和类名使用CamelCase约束，类名与文件名一致(不包括后缀)，文件所在目录与命名空间对应。


## License

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](http://opensource.org/licenses/MIT)
