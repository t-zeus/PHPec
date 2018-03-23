PHPec开发框架  [![License](https://img.shields.io/badge/license-MIT-blue.svg)](http://opensource.org/licenses/MIT)
-------------

一个极简的PHP WEB开发框架。 [点此查看详细手册](doc/manual.md)

PHPec，读作php easy, 这是多年前写过的一个MVC框架的名字。为了纪念，故继续取该名字，目标是做出一个易用、易学、易扩展的轻量WEB开发框架。

**require**: PHP5.5+ || PHP7

> 本项目使用dev分支作为开发分支，阶段可用版本在master分支，并以版本号打tag。


## 特性

- 仿koa经典的“洋葱模型”中间件模式。

下面是其执行流程示意图

![flow](https://raw.githubusercontent.com/tim1020/PHPec/dev/doc/flow.png)

- 内置自动规则路由，支持QUERY_STRING,PATHINFO及RESTFUL方式

- 支持自动依赖注入

- 提供WEB开发基本模块(待逐步添加完善)


### 中间件

中间件为请求处理的必经路径，每个中间件为一进一出两个方法，采用先进后出方式。通常负责处理输入输出、权限认证等。

### 组件

组件定义为实现一个功能的普通模块类，可以在需要时被自动注入，常用于逻辑处理、数据处理等。

### $ctx

$ctx为贯穿整个请求流程的上下文对象，即App对象本身。开发者可以在中间件或控制器方法中使用$ctx来读取或设置一些属性。

## 快速开始

PHPec使用composer管理依赖，请先安装composer（安装及使用请参考 [composer中文网](https://docs.phpcomposer.com/) ) 。

- 创建一个空目录作为你的项目目录并进入此目录

- 执行 ```composer require tim1020/phpec```获取 phpec及其依赖。

- 将 vendor/tim1020/phpec/example/* 复制到项目目录

- 配置web server，将document_root指向 public/目录

如果一切正常，就可以在浏览器中访问框架自带例子了

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
    public/             //web访问目录，存放入口文件及其它静态文件
        index.php       //入口文件
    composer.json 
```

### 常量

APP_PATH:  需在引入PHPec前定义项目的根目录(指向项目代码的app目录)

APP_NS:  项目根命名空间

### 命名空间和autoload

1. 使用APP_NS常量定义项目根命名空间，如 ```define('APP_NS', 'myapp')```

2. 在项目的composer.json中添加psr-4格式的autoload，如:

```
"autoload":{
    "psr-4":{
        "myapp\\":"app/"
    }
}
```

myapp为定义的项目根命名空间,这样在框架加载控制器或中间件及自动注入时，能找到相应的目标。

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
