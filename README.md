# Token auth middleware component for uniondrug/framework

## 安装

```shell
$ cd project-home
$ composer require uniondrug/token-auth-middleware
```

> 本中间件依赖其他两个组件：`uniondrug/cache` 缓存服务，用于存储token, `uniondrug/middleware` 中间件基础组件。

修改 `app.php` 配置文件，加上Cache服务，服务名称`tokenAuthMiddleware`

```php
return [
    'default' => [
        ......
        'providers'           => [
            ......
            \Uniondrug\TokenAuthMiddleware\TokenAuthServiceProvider::class,
        ],
    ],
];
```

## 配置

配置文件在 `middleware.php` 中，需注册中间件，以及进行中间件配置。

```php
return [
    'default' => [
        // 应用定义的中间件
        'middlewares' => [
            ...
            // 注册名为token的中间件
            'token' => \Uniondrug\TokenAuthMiddleware\TokenAuthMiddleware::class,
        ],

        // 将token中间件放在全局中间列表中
        'global'      => [
            'token', 'cors', 'cache', 'favicon', 'trace',
        ],

        ...

        // Token中间件的参数设置
        'token'       => [
            // 白名单，这个列表内的地址不需要认证，通常放登录接口等地址
            'whitelist' => [
                '/show'
            ],
            'ttl' => 7 * 86400, // 有效期7天，连续7天不登录将失效
        ],
    ],
];
```

## `tokenAuthService` 服务的接口

* `issueToken($userId, $username, $userMobile)`

颁发Token，在应用中为用户颁发Token，一般为登录成功后调用

* `revokeToken($tokenKey)`

注销Token，一般为在主动退出时调用

* `getUserId()`

返回经过认证的userid

* `getUsername()`

返回经过认证的username

## TokenAuthMiddleware的一般使用说明

1、按上述步骤安装配置中间件；

2、发布登录认证接口，并且将登录接口设置为`whitelist`；

3、用户登录认证，认证成功后，调用`issueToken()`方法为该用户颁发token，并且将token返回给用户；

4、用户携带token，访问其他需要鉴权的接口；

5、在其他服务接口中，从请求头`X-UserId`和`X-Username`中获取用户身份信息；
