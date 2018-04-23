PHPec开发框架  [![License](https://img.shields.io/badge/license-MIT-blue.svg)](http://opensource.org/licenses/MIT)
-------------

一个极简的PHP WEB后端开发框架。 [点此查看使用手册](doc/manual.md)

PHPec，读作php easy, 目标是做出一个易用、易学、易扩展的轻量WEB后端开发框架。

**require**: PHP5.6+ || PHP7

> 本项目使用dev分支作为开发分支，阶段可用版本在master分支，并以版本号打tag。


## 特性

- 仿koa经典的“洋葱模型”中间件模式
- 支持mvc开发模式
- 内置自动规则路由，支持QUERY_STRING,PATHINFO及RESTFUL方式
- 支持自动依赖注入
- 自动生成基于PDO的Model对象，并支持依赖注入或Facade方式访问
- 内置JWT和基本session认证
- 提供足够的扩展性，支持自动定义模板引擎、路由、中间件等。


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
$app -> use('PHPec\middleware\JWT');
$app -> run();
```

> PHPec同样支持使用composer来管理，你只需要使用```composer require tim1020/phpec```来初始化你的项目，然后在入口文件中使用composer的autoload.php来代替框架的autoload.php即可。

> composer安装和使用请参考 [composer中文网](https://docs.phpcomposer.com/) ) 

## 约定

PHPec使用约定大于配置为原则，在使用时，需注意遵守。

### 常量

APP_PATH:  需在引入PHPec前定义项目的根目录(指向项目代码的app目录)

APP_NS:  项目根命名空间

### 命名空间、目录和autoload

1. 使用APP_NS常量定义项目根命名空间，如 ```define('APP_NS', 'myapp')```
2. 根命名空间对应项目的app目录
3. 视图模版放在APP_PATH.'/view/'目录
4. 控制器、中间件、service的命名空间固定为其目录名，可以在目录中添加多层目录来表示多层的命名空间。文件所在目录与命名空间对应。

```
namespace myapp\controller; //对应APP_PATH.'/controller/'目录

namespace myapp\controller\user; //对应APP_PATH.'/controller/user'目录

namespace myapp\middleware;  //对应APP_PATH.'/middleware/'

namespace myapp\service;  //对应APP_PATH.'/service/'
```

> 如果你直接引用框架的autoload.php来使用，框架会自动加载项目的类文件

> 如果你使用composer来加载，你可能需要自行在你的项目composer.json文件中加入对项目类的autoload声明。

### 命名

- 常量定义全大写，下划线分隔
- 一般文件(如函数库、模板)，使用全小写文件名，下划线分隔。
- 全局函数名使用全小写文件名，下划线分隔，
- 类名（包括接口）、类文件名，并使用PascalCase格式。并且文件名与类名一致，专有名词可全大写。
- 类的方法名、内部变量名 使用camelCase
- 请求参数名、输出响应字段、数据库字段名，使用全小写，下划线分隔。
- 配置项目，使用全小写，下划线分隔

## License

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](http://opensource.org/licenses/MIT)
