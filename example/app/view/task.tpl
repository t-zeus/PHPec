<!DOCTYPE html>
<html lang="zh-CN">
  <head>
    <meta charset="UTF-8">
    <title>
      PHPec demo
    </title>

  </head>
  <body>

  <header>
  <div>
    <h1>TODO @PHPec</h1>
  </div>
  </header>

  <div>
    <?php
    foreach($ctx->body as $k=>$v){
      printf('<h3>%s</h3>',$v['title']);
    }
    ?>
    
  </div>

  </body>
</html>

