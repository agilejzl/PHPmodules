<?php
/**
 * 这里存放固定不用调整的环境配置，区别于 .env 文件
 */

return [
    // 以下存放没有差异的系统配置
    'default' => [
        'web_front_host' => 'http://127.0.0.1:8000',
    ],

    // 以下存放有环境差异的配置，优先级比上面 default 高
    'dev' => [
        'web_front_host' => 'http://localhost:8000',
    ],
    'test' => [
        'web_front_host' => 'http://test.PHPmodules.com',
    ]
];
