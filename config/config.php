<?php

use Lijinhua\Email\Storage\CacheStorage;

return [

    /**
     * 调试模式
     *
     * 调试模式下不会调用第三方网关发送短信
     * 可以通过api接口获取当前验证码信息
     */
    'debug'     => env('EMAIL_DEBUG', false),

    /**
     * 调试模式下的固定测试验证码
     */
    'code_test' => '666666',

    /**
     * 验证码
     */
    'code'      => [
        'length'       => 5, // 长度
        'validMinutes' => 5, // 有效期(分钟)
        'maxAttempts'  => 0, // 最大尝试输错次数,超过将重新生成验证码
    ],

    /**
     * 存储器
     */
    'storage'   => CacheStorage::class,

    /**
     * 邮件视图
     */
    'views'     => [
        'code' => resource_path('views/emails/code.blade.php'),
    ],

];