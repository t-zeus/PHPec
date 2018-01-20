自动路由中间件
--------------

这是由框架自动调度的中间件，在所有开发者自定义的中间件的最后被调度，提供了路由到controller目录指定resource文件执行指定action的功能。

如果你需要使用自己的路由中间件，可使用$app->use();阻止进入内置路由。

```
$app->use('MyRouter'); //或者使用函数方式，不调用$ctx->next从而不进入内置Router
$app->use();
$app->run();
```

## 路由方式

内置路由中间件，支持三种路由方式（使用 ROUTER_TYPE常量定义，默认为 1）：

- 1 query_string（c=resource&a=action） 

- 2 path_info (/resource/action) 

- 3 RESTful(Method /resource)  //请求方法作为acton，PATH支持两层资源定义，如：/shop/1/product/2

> 如果需要使用path_info或RESTful,你可能需要配置一下nginx。

Router会根椐转换出来的resource和action，在项目的controller目录找到并加载resource对应的文件名，然后执行类中的action对应的方法。

如果资源文件不存在，则尝试用any.php文件和Any类替代，同样，如果类中不存在action对应的方法，也会尝试用_any方法替代

如果路由失败，会调用$ctx->res()响应一个404,如有必要可自行拦载并转换成需要的格式。

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


## 改变默认路由行为

内置Router使用$ctx -> route_param中的参数(来自server参数)进行路由dispatch，也即是开发者有能力在路由dispatch之前修改这些参数，以改变路由行为，以下是一个简单的路由别名处理例子。

```
//如果不作处理，默认 type=1，访问 /?c=User&a=Profile时会路由到 User -> profile方法
//下面增加一个中间件改变这种行为，修改后，访问/?c=User&a=Profile会被重定向为/Shop/list
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