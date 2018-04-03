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
     * 有效时长
     *
     * @var int
     */
    public $ttl;
}
