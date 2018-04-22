PHPec使用手册
----------

想快速了解PHPec的基本使用，请参考 [【这里】](../README.md)

## 目录结构

### 框架目录结构

```
phpec\
    doc/      #文档目录
    example/  #入门例子
    src/      #框架源码
      | component/    #内置组件目录
      | interfaces/   #框架使用的接口定义
      | middleware/   #框架内置中间件
      | App.php       #框架内核
      | autoload.php  #引导文件，非composer方式使用时需调用
      | bootstrap.php #composer方式引导文件，composer方式时自动调用
```

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

## 项目配置

框架提供了一个Config组件进行配置项目的读取，你也可以自定义Config服务来进行改写读取方式（比如修改为指定的mc服务器来读取）。Config可以通过DITrait来自动注入到相应的中间件或控制器、其它服务组件。

> 关于自定注入、组件或自定义服务的介绍请参考后面的内容。

内置的Config组件，约定的配置文件为 APP_PATH/config/app.php，并期望该文件返回一个多维数组作为配置项目：

```
//APP_PATH/config/app.php

//如果有多个配置，可先在app.php中读入并合并

return [
    'app_name'=> 'my app',
    'log'   => [
        'level' => 1 | 2 | 4,
        'path'  => '/tmp',
    ],
    'db'    =>[
        'dsn'       => 'mysql:host=localhost;dbname=phpec;charset=utf8mb4',
        'user'      => 'root',
        'password'  => ''
    ],
];

```

Config接口只提供一个get($key, $default = null)方法，该方法可以读取指定的配置项目：

```
//$this -> Config方式调用需要引入DITrait来添加自动依赖注入方法

//读取app_name字段内容，如果没有或为空，返回MyApp
$this -> Config -> get('app_name','MyApp'); 

$this -> Config -> get("log.path"); //读取log字段下的path，可支持多层，用.分隔
```

## 日志处理



## 中间件

PHPec的中间件与KOA的类似，每一次请求都会依次经过每一声明使用的中间件的enter方法处理，再使用后进先出的方式经过leave方法的处理。

比如：
```
$app -> use('M1');
$app -> use('M2');
```
以上的调用，方法执行顺序是： M1->enter(), M2 -> enter(), M2 -> leave(), M2 -> leave()

### 如何使用中间件

要使用中间件，需要在入口文件中使用```$app -> use()```方法来声明,包括以下几种方式：

- 闭包

使用闭包方式声明中间件，需要手动调用```$ctx->next()```方法来调用下一中间件

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

框架目前内置了几个基础中间件，包括CommonIO、Router、JWT、ViewRender。

其中CommonIO和Router由框架自动调用，其它内置中间件在需要时由开发者手工调用，方式是:

```
$app -> use('PHPec\middleware\JWT'); //需指定命名空间
```

- 自定义中间件

要使用自定义中间件，只需在入口文件中使用中间件名字进行声明即可：

```
//仅使用名字，无需指定命名空间，框架会自动在app/middleware目录加载
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

如果要跳过默认加载的内置Router中间件，可使用```$app -> use(false)```

如果需要在某一中间件中根椐一定规则跳过后续中间件（如认证不通过时不执行后续处理），可以在enter方法中```return false``` 

> 使用闭包方式时，如果没有显式使用 ```$ctx -> next();```方法，则后续的中间件也不会被执行。


### 内置中间件介绍

+ CommonIO

该中间件负责一些通用的输入输出处理，框架会默认在所有其它中间件之前自动调用该中间件，开发者无需手动调用，也无法取消。

首先，对输入参数进行处理，将$_GET,$_POST,$_SERVER进行影射，比如：

$ctx -> _G 是$_GET的只读影射，用以保存原始的$_GET请求参数。同样，$ctx -> _H,$ctx -> _P, $ctx -> _C分别对应请求header，$_POST参数，$_COOKIE

另外，提供了$ctx -> req数组，分别保存了上述的请求内容，不同的是该数组可以被修改。

> CommonIO会删除全局变量 $_GET,$_POST,$_REQUEST,$_SERVER

在请求处理完成，最后输出前，会由CommonIO的leave方法处理，该方法主要是对设置的响应头和内容进行解释和封装输出。

    1. 可以通过$ctx -> setHeader() 方式设置响应头
    2. 通过$ctx -> body = xxx设置响应body
    3. 直接通过$ctx -> res('body','code')方式输出内容和响应码

开发者可以根椐自己项目的需要，定义和使用自己的输入输出处理中间件。比如输入的安全过滤、统一控制输出格式、添加模板处理引擎等。

+ Router

路由中间件，负责根椐请求参数将请求分发到相应的控制器，该中间件提供基本的自动路由方法，无需编写路由表，支持querystring,pathinfo,restful。

> 该中间件默认总是作为最后一个中间件被声明使用，与CommonIO不同，路由中间件可以被屏蔽。

+ JWT

该中间件提供基于JWT的请求认证方案，关于JWT的介绍，请自行上网搜索，PHPec的JWT中间件处理流程如下：

a. 请求到达时，检查是否带有正确的token，如果没有或者token验证不通过，返回401，验证通过则继续后续处理。

b. 请求没有token，且使用带有account和password参数的post方式请求，表示进行授权请求，授权请求成功后，会生成token，并返回。

> 处理授权请求时，框架通过```$this -> Auth -> verify($account, $password)```方法来判断授权是否通过，框架本身不提供Auth组件的实现，开发者需要自行根椐\PHPec\intervaces\Auth接口实现一个Auth组件。


> PHPec内置的JWT支持标准的使用header方式传递token，也支持使用cookie方式传递。默认为header方式，要使用cookie方式，只需在配置中加入 jwt['use_cookie'] => true即可。

+ ViewRender

考虑当前主流的开发模式都是前后端分离，PHPec的侧重点也会是在后端API开发方面，所以仅提供一个简陋的使用PHP本身的模板引擎，后续再进行完善。

### 编写自定义中间件

编写自定义中间件，需遵守以下规则：

- 文件需放在APP_PATH的middleware目录下，类名、文件名与中间件名称一致
- 必须实现PHPec\interfaces\Middleware接口
- 使用“myapp\middleware”命名空间（myapp为项目的根命名空间，由常量APP_NS定义）

以下是一个简单例子：

```
//APP_PATH/middleware/M1.php

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

PHPec支持使用MVC模式来开发应用，其基本处理流程是：

1. 通过内置或自定义路由中间件，根椐请求参数自动路由到controller目录的相应控制器。
2. 控制器通过框架提供的自动依赖注入方式调用Model对象来处理数据（较复杂的逻辑也可以在Model基础上封装成service服务供controller使用）
3. 获得需要返回的数据后，绑定到$ctx -> body中
4. 由特定中间件中声明的模板引擎调用相应的输出模板进行渲染输出。

作为约定，MVC模式各层文件的保存路径为：

controller 放置控制器类文件
view 放置视图模板文件
service 放置业务逻辑类

> 框架可以在自动依赖注入时自动生成基于PDO的Model, 并不需要编写简单的Model类.

### 路由

路由是指根椐请求参数，将请求分发到相应的控制器去处理的过程。

#### 内置路由中间件

框架默认自动调用内置的一个路由中间件，提供了三种路由方式，已经能满足绝大部分需求了，下面来说明一下内置路由的分发规则。

+ 路由方式

可在配置文件中使用‘route_type => TYPE’来定义路由方式，TYPE包括以下三种：

QUERY_STRING

根椐GET参数中的c和a参数来分发，c指定控制器，a指定要执行的控制器的方法。 （c和a是约定的值，如果需要使用其它参数名，可使用nginx的rewrite来处理）

PATHINFO

根椐路径参数来反发，其中路后一段为action,其它的为controller，比如：
```
/User/list          => c=User, a=list
/product/Tools/add  => c=product/Tool,a=add  (即contrller/product/Tool.php)
```

RESTFUL

REST化的请求支路由，即根椐请求路径和请求参数来分发。

其中的请求方法（全小写），对应到要执行的控制器的方法，路径解释为控制器，比如：

```
GET /User                 => c=User,a=get
POST /Shop/12/Product/22  => c=Shop/Product,a=post
```

> 路径的格式为： /资源/:id/子资源/:id, 框架会将解释出来的资源及其ID绑定到$ctx -> resId中，在控制器中可以直接使用。

> 内置路由对参数的处理原则是区分大小写。

+ 默认匹配

在解释controller和action过程中，有一些默认匹配规则，包括：

a. 当相应的controller文件没找到时，使用Any.php代替。（即无法命中时执行c=Any控制器）

b. 当相应的action方法没有找到时，使用function _any($ctx)来代替，如果_any方法也没有定义，返回404响应。

#### 自定义路由中间件

如果你需要自定义路由，只需要按“编写自定义中间件”的方法来实现，然后在入口文件中进行调用，并使用```$app->use();```来跳过内置路由。

具体的路由实现可参考内置路由中间件，这里仅演示使用方法：

+ 实现一个自定义路由中间件

```
//APP_PATH/middleware/MyRouter.php

namespace mapp\middleware;
class MyRouter implements \PHPec\interfaces\Middleware
{
    use \PHPec\DITrait; //拥有自动依赖注入功能

    public function enter($ctx)
    {
        //根椐$ctx->req来分发请求到不同的控制器中
        //例如根椐$ctx -> req['post']['res']来调用相应的Controller
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


### 控制器

PHPec对控制器的约束比较宽松，以下是使用内置路由时，编写控制器的注意事项目：

+ 控制器类放在app/controller目录，在此目录下可以继续增加下层目录，控制器文件名与类名对应。
+ 在controller目录的类使用myapp\controller命名空间（myapp可以通过APP_NS定义），如果有下层目录则相应的增加命名空间的层次。
+ 控制器方法使用字母开头，全小写，不带任何约定的前缀或后缀。
+ _any方法被留着无法命中时的默认匹配方法。
+ 方法定义，需带有$ctx作为参数

另外，框架提供了一个简单的控制器基类（\PHPec\BaseControl），你可以通过继承它来实现你的控制器，现阶段，该基类仅提供两个hook功能：

+ 如果控制器有实现 _before() 方法，会在执行控制器方法前被调用_
+ 如果有提供 _after()方法，会地执行控制器方法之后自动调用。

以下是一个基本例子:
```
//APP_PATH/controller/Task.php

<?php
namespace myapp\controller;

/**
 * 继承\PHPec\BaseControl,  _before($ctx)和_after($ctx)会被自动调用，如果不需要此特性，也可以不继承
 * 如果多个Controller都需要执行同一个_before或_after
 * 可以自行再实现一个Base类去继承\PHPec\BaseControl，然后其它Controller再继承它。
 */
class Task extends \PHPec\BaseControl
{   
    //使用自动依赖注入特性
    use \PHPec\DITrait;
    
    //a=add
    public function add($ctx)
    {
        //仅演示，对于输入参数请注意过滤，保证安全
        $ctx -> body .= 'hello'.$this -> _G['name']; 
    }    

    //default action
    function _any($ctx){
        
    }


    function _before($ctx)
    {
        $ctx -> body ='[before]';
    }
}

```


> 如果你使用了自定义路由，请自行根椐路由的处理来处理controller的约束。

### 模板和视图

#### 使用内置的模板引擎

#### 如何编写自己的模板引擎。


### Model对象

#### PDO

#### *Model对象

#### 事务 


## 自动依赖注入

框架使用DITrait来提供了对服务组件的自动依赖注入功能，可以用在中间件、组件、控制器等。

要使用该功能，首先需要引入DITrait，然后直接使和$this -> Xxxx的方式来完成自动依赖注入,例如：

```
namespace myapp\service
class MyService{
    use \PHPec\DITrait;
    function doit(){
        $this -> Config -> get('cc'); //自动注入了Config服务
        $this -> UserModel -> getAll(); //自动生成UserModel并注入
    }
}
```

注意事项：

+ 使用前先使用use引入。
+ 使用$this -> Xxx方式注入，Xxx为组件类名，其中只有大写字母开头的才会被自动注入
+ 扫描顺序为： 项目service目录 > 框架component目录，
+ 同名时，自定义组件会覆盖内置组件，利用该特性，可以扩展框架内置组件，比如定义一个Logger将日志发送到队列处理。
+ 由于内置组件在框架中也会经常用到，覆盖内置组件时，需注意必须遵守相应的接口。
+ 使用$this -> XxxModel，会自动生成并注入以xxx为表名的数据Model对象。
+ 你也可以在配置中侃用container_bind => [interface => impl] 来声明要注入接口的具体实现类（带命名空间）


## 编写和使用service

### 接口约定

- Config
- Logger
- Middleware
- Auth

## $ctx 对象
$ctx是App本身的引用，在中间件和控制器中，都作为上下文对象进行传递。方便在方法中进行相应的数据获取和处理。


## session处理

框架内置了一个简单的Session组件，该组件主要作用是对$_SESSION的基本封装，并根椐配置项（session.handler）来调用相应的SessionHandler。

该组件提供也以下基本方法：
```
function get($key);     //获取一个 $_SESSION[$key]
function getAll();      //获取全部 $_SESSION
function set($key,$val);//设置 $_SESSION[$key] = $val
function delete($key);   //删除一个 unset($_SESSION[$key])
function commit();       //session_write_close();
```

> 如果不指定SessionHandle，则使用php.ini中的相关配置，默认为File方式的Handler。

session.handler可以指定框架提供的SessionHandler，也可以指定自定义的SessionHandler。

要实现一个自定义的SessionHandler，方法是：

1. 编写自定义的SesssionHandler，并放置在APP_PATH/service/目录
2. 实现\SessionHandlerInterface接口

以下是一个实现模板：

```
//APP_PATH/service/MySess.php

namespace PHPec\component;

class MySess implements \SessionHandlerInterface
{
    public function open($savePath, $sessionName)
    {
        //session_start时调用，用来进行session初始化
    }

    public function close()
    {
        return true;
    }

    public function read($id)
    {
        //根椐$id，读取对应的session值（将数据load到$_SESSION）
        //return getSessById($id)
    }

    public function write($id, $data)
    {
       //将$_SESSION保存
    }

    public function destroy($id)
    {
        //销毁整个session的数据
    }

    public function gc($maxlifetime)
    {
        //根椐$maxlifetime的值来执行过期数据清理
    }
}
```

完成后，在配置中指定 session.handler => 'MySess' 即可使用该自定义的SessionHandler

> 在Session组件初次被生成（注入）时，session_start（）会自动调用。




