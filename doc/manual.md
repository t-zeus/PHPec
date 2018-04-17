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

其中CommonIO和Router由框架自动调用，ViewRender和其它待加入的内置中间件，需手工调用，方式是:

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

PHPec使用MVC模式来开发应用, 首先通过内置或自定义路由中间件，根椐请求参数自动路由到controller目录的相应控制器。控制器通过框架提供的自动依赖注入方式调用xxModel对象来处理数据（较复杂的逻辑也可以在Model基础上封装成service服务供controller使用）,最后，获得需要返回的数据后，经由模板引擎调用相应的输出模板进行渲染输出。

作为约定，MVC模式各层文件的保存路径为：

controller 放置控制器类文件
view 放置视图模板文件
service 放置业务逻辑类

> 框架会自动生成并注入xxModel类，并不需要编写简单的Model类。

### 路由

路由是指根椐请求参数，将请求分发到相应的控制器去处理的过程，框架默认自动调用内置的一个路由中间件，如果你需要自定义路由，只需要按“编写自定义中间件”的方法来实现，然后在入口文件中进行调用，并使用```$app->use();```来跳过内置路由。

具体的实现可参考内置路由中间件，这里仅演示使用方法：

+ 实现一个自定义路由中间件(app/middleware/MyRouter.php)

```
namespace mapp\middleware;
class MyRouter implements \PHPec\interfaces\Middleware
{
    use \PHPec\DITrait; //拥有自动依赖注入功能

    public function enter($ctx)
    {
        //根椐$ctx->req来分发请求到不同的控制器中，例如配合控制器基类可以实现自动的简单增删改查接口
    }
    public function leave($ctx)
    {
        //do nothing
    }
}
```

+ 在入口文件（public/index.php）调用

```
$app = new PHPec\App();
//$app -> use('Middleware'); //要使用的其它中间件
$app -> use('MyRouter');//自定义路由中间件
$app -> use(); //用空参数或false，声明跳过后续的中间件，即内置的Router
$app -> run();
```

一般情况下，内置的路由中间件提供了三种路由方式，已经能满足绝大部分需求了，下面来说明一下内置路由的分发规则。

+ 路由方式

+ 默认匹配


### 控制器

PHPec对控制器只有一些基本的约束:

+ 控制器类放在app/controller目录，在此目录下可以继续增加下层目录，文件名与类名对应。
+ 在controller目录的类使用myapp\controller命名空间（myapp可以通过APP_NS定义），如果有下层目录则相应的增加命名空间的层次。

### \PHPec\BaseControl

框架提供了一个简单的控制器基类，你可以通过继承它来实现你的控制器，现阶段，该基类仅提供两个_hook功能，即控制器如果有提供 _before() 方法，会在执行控制器方法前调用_before方法。同样，如果有提供 _after()方法，会地执行控制器方法之后自动调用。

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





