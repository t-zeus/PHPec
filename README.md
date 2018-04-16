PHPec开发框架  [![License](https://img.shields.io/badge/license-MIT-blue.svg)](http://opensource.org/licenses/MIT)
-------------

一个极简的PHP WEB开发框架。 [点此查看详细手册](doc/manual.md)

PHPec，读作php easy, 目标是做出一个易用、易学、易扩展的轻量WEB开发框架。

**require**: PHP5.6+ || PHP7

> 本项目使用dev分支作为开发分支，阶段可用版本在master分支，并以版本号打tag。


## 特性

- 仿koa经典的“洋葱模型”中间件模式
- 支持mvc开发模式
- 内置自动规则路由，支持QUERY_STRING,PATHINFO及RESTFUL方式
- 支持自动依赖注入
- 支持PDO操作数据库，自动生成并注入 *Model 对象。
- 提供WEB开发基本模块(待逐步添加完善)
- 提供足够的扩展性，支持自动定义模板引擎、路由、中间件等。

### 中间件

中间件为每一请求都必经路径，每个中间件为一进一出两个方法，采用先进后出方式。通常用来处理输入输出转换、权限认证、路由分派等。

### 服务组件

服务组件定义为完成一个单一功能或提供一个服务的普通模块类，可以在需要时被自动注入，常用于商业逻辑处理、数据处理等。

### $ctx

$ctx为贯穿整个请求流程的上下文对象，亦即App对象本身。开发者可以在中间件或控制器方法中使用$ctx来读取或设置一些全局属性。

## 快速开始

下载PHPec代码包到指定目录(比如： ~/myapp/libs/phpec/)，创建一个入口文件引用即可。

> 例子可参考 phpec/exmaple

### 入口 

```
//index.php

//定义APP_PATH和APP_NS常量，对应项目代码目录及项目根命名空间
define('APP_PATH',__DIR__.'/../app');
define('APP_NS', 'myapp');

//加载启动文件
require 'path_to_phpec_src/autoload.php';
//生成应用，加载中间件，启动应用
$app = new \PHPec\App();
$app -> use('PHPec\middleware\ViewRender');
$app -> run();
```

> PHPec同样支持使用composer来管理，你只需要使用```composer require tim1020/phpec```来初始化你的项目，然后在入口文件中使用composer的autoload.php来代替框架的autoload.php即可。

> composer安装和使用请参考 [composer中文网](https://docs.phpcomposer.com/) ) 

## 约定

PHPec使用约定大于配置为原则，在使用时，需注意遵守。

### 项目目录结构

```
APP_SRC/
    app/                //应用代码
        config/             //配置文件目录
            app.php         //主配置文件
        controller/         //控制器目录
        middleware/         //中间件目录
        service/            //服务组件目录，自动注入的查找目录
    runtime/            //运行时存储目录，包括log和cache，需可写权限
        cache/
        log/
    vendor/             //composer安装的库，包括PHPec
    public/             //web访问目录，存放入口文件及其它静态文件
        index.php           //入口文件
    composer.json 
```

### 常量

APP_PATH:  需在引入PHPec前定义项目的根目录(指向项目代码的app目录)

APP_NS:  项目根命名空间

### 命名空间、目录和autoload

1. 使用APP_NS常量定义项目根命名空间，如 ```define('APP_NS', 'myapp')```

2. 根命名空间对应项目的app目录
3. 视图模版放在APP_PATH.'/view/'目录
4. 控制器、中间件、service的命名空间固定为其目录名，可以在目录中添加多层目录来表示多层的命名空间。

```
namespace myapp\controller; //对应APP_PATH.'/controller/'目录

namespace myapp\controller\user; //对应APP_PATH.'/controller/user'目录

namespace myapp\middleware;  //对应APP_PATH.'/middleware/'

namespace myapp\service;  //对应APP_PATH.'/service/'
```

> 如果你直接引用框架的autoload.php来使用，框架会自动加载项目的类文件

> 如果你使用composer来加载，你可能需要自行在你的项目composer.json文件中加入对项目类的autoload声明。


### 配置文件 

- 配置文件保存在APP_PATH/config目录

- 使用app.php作为主配置文件，如有多个配置，需手动将其它配置都读入到app.php进行合并

- 配置文件使用 ```return 数组```来返回配置

```
//app.php
return [
    'log' => [
        'path' => '/tmp'
    ],
    'app_name' => 'phpec demo'
];
```

### 文件名与类名

- 所有的类（包括接口定义），都应该每一个类对应一个文件，类名与文件名都使用CamelCase格式，并一一对应。
- 文件所在目录与命名空间对应。
- 其它类型的文件，如函数库、模板，使用全小写的文件名。
- 模板文件以** “.tpl” ** 结尾。


## License

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](http://opensource.org/licenses/MIT)
