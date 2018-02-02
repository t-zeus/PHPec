MySQL ORM
---------

PHPec内置了一个基于PDO实现的orm，封装了基本的CRUD操作及简单事务。

## 引入

在主入口使用 ```$app -> use('\PHPec\PdoOrm')```引用，框架会使用$ctx -> pdo 来绑定。

> 在使用之前，需要定义DB_DSN,DB_USER,DB_PASS常量，具体可参考test中的用法。

## 基本使用

引入之后，该中间件提供了常用的数据操作封装。

### map方法

    ```$ctx -> pdo -> map('table_name')```

    该方法生成一个基于指定表名的操作Dao类，提供add,update,delete,get,getOne等方法。

### query方法

    ```$ctx -> pdo -> query($sql,$param=[])```

    该方法执行一个sql语句，可以使用参数绑定的方式，比如：

    ```
    //只有一个占位符，可以直接用单一值
    $ctx -> pdo -> query('select * from a where id=?',12); 

    //多个占位符，使用数组
    $ctx -> pdo -> query('select * from a where id=? and name=?',[12,'time']);

    //in要使用(?)占位，并在该占位要使用数组替换(就算只有一值也是)
    $ctx -> pdo -> query('select * from a where id in (?)',[ [11] ] );
    $ctx -> pdo -> query('select * from a where name=? and id in (?)',['tim',[12,11]]);
    ```

    > 如果是简单的CRUD，建议使用map出来的Dao对象来操作。提供query方法是为了增加灵活性。

### transaction方法

    transaction方法用来包装一个事务,使用方法是装事务操作放到闭包里。

    ```
    $user = $ctx -> pdo -> map('user'); //生成use表的dao
    $re = $ctx -> pdo -> transaction(function(&$err) use($user){ 
        $id = $user -> add(['name':'tim']);
        $user -> update(["id=?",$id],['name':'tim1']);
        //return false
    });
    ```

    该方法的返回值是事务是否成功，如果发生错误，事务会自动回滚，如果没有任何问题，事务会自动提交。

    如果业务需要自行强行回滚，可以使用return false强制回滚，并可使用$err保存回滚的理由。

## Dao对象

使用$ctx-> pdo-> map（）方法生成出来的是一个Dao对象，该对象提供常见的CRUD方法，以下是一个例子：


```
$user = $ctx -> pdo -> map('user');
$uid = $user -> add(['name'=>'tim','type'=>'dev']);
$re =  $user -> update("id=$uid",['type'=>'prod']);
$row = $user -> getOne(['type=?','prod']);
$re = $user -> delete(['type=?','prod']);
```


- add(Array $data)

    插入一条数据，返回插入的ID，$data为表中对应的字段和值。

- delete($where)

    根据$where条件删除记录，返回false或者删除的记录数

- update($where,Array $data)

    根椐$where条件，更新$data，返回false或发生更新的记录数。

- get($where,Array $options = [])

    根椐$where条件，查询多条记录。

    $options是附加选项，包括：

    + fields:指定要查的字段，默认是*
    + sort: 排序字串,如： sort => 'id desc,age asc'
    + page: 查询的页码，默认为1
    + pageSize:  每页记录数，默认20

- getOne($where,Array $options)
    
    根椐$where条件，返回一条记录。

    $options同get，但page和pageSize无效。


### where说明

    where条件用在delete,update和get/getOne方法中,支持两种模式：

- 字符串条件表达式 ,如： a=1, a>=2

     + 该方式只支持一个条件，不能用and或or
     + 运算符支持 >,<,>=,<=,<>,=, is, is not, like, not like, in,not in
     + is/is not 只能是null 
     + 值不用引号，包括 like/not like，如： name=time和title like %abc% 都是正确的写法。
     + in/not in 用逗号分隔，如： a in 1,2,3
     + 该方式最后也会被转换为prepare方式执行

- 使用数组，第一元素为带占位符的表达式，第二元素为替换内容，如： ['a=? and b=?',[1,2]]

     + 数组第一个为带占位符的完整条件表达式，支持复杂的表达式
     + 第二个元素为用来替换占位符的内容数组，如果只有一个占位符，可直接使用值，如['a=?',1]
     + 注意保证占位数量和替换内容的个数一样
     + in的占位符使用(?),如 : ["id in (?) and name=?",[[1,2],'tim']]


### 异常

Dao对象在碰到无法完成的操作时会抛出PDOExcetion异常，开发者应该知道什么时候该捕获它。

如果使用transaction包装的操作，则异常会被系统捕获，并回滚事务。
