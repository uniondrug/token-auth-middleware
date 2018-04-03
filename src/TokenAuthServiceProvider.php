<?php

namespace Uniondrug\TokenAuthMiddleware;

use Phalcon\Di\ServiceProviderInterface;

class TokenAuthServiceProvider implements ServiceProviderInterface
{
    public function register(\Phalcon\DiInterface $di)
    {
        $di->set(
            'tokenAuthService',
            function () {
                return new TokenAuthService();
            }
        );
    }
}
