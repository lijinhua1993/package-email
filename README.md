# laravel 邮件发送

## 安装
```shell
composer require lijinhua/email
```
发布配置文件
```shell
php artisan vendor:publish --provider="Lijinhua\Email\ServiceProvider"
```
创建邮箱验证码模板

config/email.php

```php
    /**
     * 邮件视图
     *
     * 将调用 view('...') 格式参考laravel视图文档
     */
    'views'     => [
        'code' => 'emails.code',
    ],
```

模板示例: resources/views/emails/code.blade.php

```
<h1>
    验证码：<b>{{ $code }}</b>，该验证码{{ $validMinutes }}分钟内有效。为了保障您的账户安全，请勿向他人泄漏验证码信息。
</h1>
```


## 使用方法

### 发送验证码
```php
// 发端验证码
\Email::sendCode("XXX@qq.com");
```

### 校对验证码
```php
\Email::checkCode("XXX@qq.com","666666");

// 清除验证码缓存,防止重复利用
\Email::clearCode("XXX@qq.com",86);
```


> 环境变量(EMAIL_DEBUG=true)时验证码为666666
> 且不会真实调用第三方邮箱服务商发送邮件