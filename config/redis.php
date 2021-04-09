<?php

// +----------------------------------------------------------------------
// | Redis
// +----------------------------------------------------------------------

return [
    //公共数据
    'common_data' => [
        // 服务器地址
        'host'   => '47.94.154.10',
        //测试
        'port'   => '6379',
        'password'=> "redis1qa",
        //正式
//        'port'   => '16379',
//        'password'=> "sdBoonRedis711&!!",
        'timeout' => 86400,
        'select' => 0,
    ],
    //用户登陆使用
    'user_login' => [
        // 服务器地址
        'host'   => '47.94.154.10',
        //测试
        'port'   => '6379',
        'password'=> "redis1qa",
        //正式
//        'port'   => '16379',
//        'password'=> "sdBoonRedis711&!!",
        'timeout' => 86400,
        'select' => 1,
    ],
    //场所使用
    'place' => [
        // 服务器地址
        'host'   => '47.94.154.10',
        //测试
        'port'   => '6379',
        'password'=> "redis1qa",
        //正式
//        'port'   => '16379',
//        'password'=> "sdBoonRedis711&!!",
        'timeout' => 86400,
        'select' => 3,
    ],
];