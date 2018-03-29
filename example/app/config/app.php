<?php
//配置入口，如果有多个配置文件，请在该文件读入其它配置，并合并成一个数组返回
return [
    'route_type' => 'QUERY_STRING',
    'greet'      => 'Hello PHPec',
    'log'   => [
        'level' => 255,
        //'path'  => '',
    ],
    'db'    =>[
        'dsn'       => 'mysql:host=localhost;dbname=phpec;charset=utf8mb4',
        'user'      => 'root',
        'password'  => ''
    ]
];
?>