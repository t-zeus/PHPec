Logger
------

PHPec内置了一个logger类，可以使用 ```$app -> use('\PHPec\Logger')```引入,引入后会绑定在$ctx -> logger中，该类提供几个输出日志的方法：

```
$ctx -> logger -> debug();
$ctx -> logger -> info();
$ctx -> logger -> warn();
$ctx -> logger -> error();
```

$ctx->logger是一个可扩展的实现，默认的实现，会将日志以“类型_yyyymmdd.log”为文件名保存在logs目录。哪些日志要输出，使用常量LOG_LEVEL来定义

debug(1), info(2),warn(4),error(8)，比如： LOG_LEVEL = 15表示全部(1+2+4+8)

如果你想改变$ctx->logger的输出方式，可以在引入时传入一个实现了\PHPec\LogWriter接口的writer作为参数,如：

```
class MyLogWriter implements \PHPec\LogWriter{
	//$msg为日志内容，$type为类型(debug,info,warn,error)
	function write($msg,$type){
		//具体处理，比如发到消息队列
	}
}
$app -> user("\PHPec\Logger",new MyLogWriter());
```
