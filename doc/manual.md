PHPec使用手册
----------

想快速了解PHPec的基本使用，请参考 [【这里】](../README.md)

## 中间件

PHPec的中间件与KOA的类似，每一次请求都会依次经过每一声明使用的中间件的enter方法处理，再使用后进先出的方式经过leave方法的处理。

比如：

```
$app -> use('M1');
$app -> use('M2');
```
以上的调用，方法执行顺序是： M1->enter(), M2 -> enter(), M2 -> leave(), M2 -> leave()


### 如何使用中间件

要使用中间件，需要在入口文件中使用```$app -> use```方法来声明,包括以下几种方式：

- 闭包

使用闭包方式声明中间件，需要手动调用$ctx->next方法来调用下一中间件

```
$app -> use(
  function($ctx){
    //do enter
    $ctx -> next();//进入下一个中间件，完成其它中间件处理后才会继续执行后续代码
    //do leave
  }
);
```

- 内置中间件

框架目前内置了几个基础中间件，包括CommonIO、Router、ViewRender。

其中CommonIO和ViewRender由框架自动调用，ViewRender和其它待加入的内置中间件，需手工调用，方式是:

```
$app -> use('PHPec\middleware\ViewRender'); //需指定命名空间
```

- 自定义中间件

要使用自定义中间件，只需在入口文件中使用中间件名字进行声明即可：

```
$app -> use('MiddlewareName');
```

- 使用数组方式引入多个

如果有多个中间件需引用，可使用数组方式：

```
$app -> use(['Middle1','Middle2']);
```

- 带参数引用

如果自定义中间件中，使用了带参数的构造函数（不建议），可在调用时传入参数：

```
$app -> use('MiddleName','param');
```

- 跳过后续中间件

如果要跳过默认加载的内置Router中间件，可使用```$app -> use(false);```

如果需要在中间件中根椐一定规则跳过后续中间件（如认证不通过时不执行后续处理），可以在enter方法中```return false``` 

使用闭包方式时，直接去掉```$ctx -> next();``` 即可


### 内置中间件介绍

+ CommonIO

该中间件负责一些通用的输入输出处理，在enter时对输入进行一个简单的封装，在leave时对response进行简单封装。框架会默认在所有其它中间件之前自动调用该中间件，开发者无需手动调用，也无法取消。

开发者可以根椐自己项目的需要，定义和使用自己的输入输出处理中间件。比如输入的安全过滤、统一控制输出格式、添加模板处理引擎等。

+ Router

路由中间件，负责根椐请求参数将请求分发到相应的控制器，该中间件提供基本的自动路由方法，无需编写路由表，支持querystring,pathinfo,restful。

该中间件默认总是作为最后一个中间件被声明使用，与CommonIO不同，路由中间件可以被屏蔽。

+ ViewRender

考虑当前主流的开发模式都是前后端分离，PHPec的侧重点也会是在API开发方面，所以仅提供一个简陋的使用PHP本身的模板引擎。

该中间件由开发者根椐需要自行手动调用。

### 编写自定义中间件

编写自定义中间件，需遵守以下规则：

- 文件需放在APP_PATH的middleware目录下，类名、文件名与中间件名称一致
- 必须实现PHPec\interfaces\Middleware接口
- 使用“myapp\middleware”命名空间（myapp为项目的根命名空间，由常量APP_NS定义）

以下是一个简单例子：

```
namespace mapp\middleware;
class M1 implements \PHPec\interfaces\Middleware
{
    use \PHPec\DITrait; //拥有自动依赖注入功能

    public function enter($ctx)
    {
        $ctx -> stime = microtime(1);
        $this -> Logger -> debug('M1 enter');
    }
    public function leave($ctx)
    {
        $this -> Logger -> debug('M1 leave');
        $end = microtime(1);
        $es = $end - $ctx -> stime;
        $this -> Logger -> event('start=%s,end=%s,es=%s',$ctx -> stime, $end, $es);
    }
}
```


## MVC模式

### 路由

#### 使用内置路由

#### 编写自定义路由

### 控制器

### \PHPec\BaseControl

### 模板和视图

#### 使用内置的模板引擎

#### 如何编写自己的模板引擎。

### Model对象

#### PDO

#### *Model对象

#### 事务 


## 自动依赖注入

## 服务组件

### 内部服务组件

- 配置读取
- Logger

### 编写项目服务组件

### 接口约定

- Config
- Logger
- Middleware

## $ctx 对象





