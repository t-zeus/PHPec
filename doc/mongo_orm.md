Mongo ORM
---------

PHPec内置了一个对mongodb操作进行简单封装的orm，提供基本的CRUD操作。

## 引入

在主入口使用 ```$app -> use('\PHPec\MongoOrm')```引用，框架会使用$ctx -> mongo 来绑定。

> 在使用之前，需要定义MONGO_DSN,MONGO_DBNAME常量，具体可参考testCase中的用法。

## 基本使用

引入之后，该中间件提供了常用的数据操作封装。

### map方法

    ```$ctx -> mongo -> map('db.coll')```

    该方法生成一个基于指定collection的操作Dao类，提供add,update,delete,get,getOne等方法。

### exec方法

    ```$ctx -> mongo -> exec($cmd)```

    该方法执行一个mongodb的cmd，用于执行CRUD不能满足的命令，比如：

    ```
    $ctx -> mongo -> exec(['ping'=>1]); 
    $ctx -> mongo -> exec([
        findAndModify: "people",
        query: { name: "Andy" },
        sort: { rating: 1 },
        update: { $inc: { score: 1 } },
        upsert: true
   ]);
    ```

## Dao对象

使用$ctx-> mongo-> map() 方法生成出来的是一个Dao对象，该对象提供常见的CRUD方法，以下是一个例子：


```
$user = $ctx -> mongo -> map('test.user'); //db.collection
$uid = $user -> add(['name'=>'tim','type'=>'dev']);
$re =  $user -> update(['_id'=>$uid],['type'=>'prod']); //第一参数是mongodb的filter
$row = $user -> getOne(['type'=>'prod']);
$re =  $user -> delete(['type'=>'prod']);
```


- add(Array $data)

    插入一条数据，返回插入的ID，$data为文档集合。

- delete($filter)

    根据$filter条件删除记录，返回false或者删除的记录数

- update($filter,Array $data)

    根椐$filter条件，更新$data，返回false或发生更新的记录数。

- get($filter,Array $options = [])

    根椐$filter条件，查询多条记录。

    $options是mongodb的附加选项，包括sort，skip，limit等

- getOne($filter,Array $options=[])
    
    根椐$filter条件，返回一条记录。

