<?php

namespace Uniondrug\TokenAuthMiddleware;

use Uniondrug\Structs\Struct;

class TokenAuthStruct extends Struct
{
    /**
     * Token串
     *
     * @var string
     */
    public $name;

    /**
     * 用户ID
     *
     * @var string
     */
    public $userId;

    /**
     * 用户名称
     *
     * @var string
     */
    public $username;

    /**
     * 用户手机号码
     *
     * @var string
     */
    public $userMobile;

    /**
     * 有效时长
     *
     * @var int
     */
    public $ttl;

    /**
     * 自定义信息
     */
    public $customInfo;
}
