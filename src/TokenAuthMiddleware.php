<?php
/**
 * 基于TOKEN的认证方式。
 */

namespace Uniondrug\TokenAuthMiddleware;

use Phalcon\Http\RequestInterface;
use Uniondrug\Middleware\DelegateInterface;
use Uniondrug\Middleware\Middleware;

/**
 * Class TokenAuthMiddleware
 *
 * @package Uniondrug\TokenAuthMiddleware
 * @property \Uniondrug\TokenAuthMiddleware\TokenAuthService $tokenAuthService
 */
class TokenAuthMiddleware extends Middleware
{
    public function handle(RequestInterface $request, DelegateInterface $next)
    {
        // 0. WhiteList
        if ($this->tokenAuthService->isWhiteList($request->getURI())) {
            return $next($request);
        }

        // 1. 提取TOKEN, return 401
        $token = $this->tokenAuthService->getTokenFromRequest($request);
        if (empty($token)) {
            $this->di->getLogger('middleware')->debug(sprintf("[TokenAuth] Unauthorized."));

            return $this->serviceServer->withError('Unauthorized', 401)->setStatusCode(401);
        }

        // 2. 校验TOKEN, return 403
        $tokenAuthStruct = $this->tokenAuthService->checkToken($token);
        if (!$tokenAuthStruct) {
            $this->di->getLogger('middleware')->debug(sprintf("[TokenAuth] Invalid Token: token=%s", $token));

            return $this->serviceServer->withError('Forbidden: Invalid Token', 403)->setStatusCode(403);
        }

        // 3. 附加信息
        $_SERVER[TokenAuthService::USERID_HEADER] = $tokenAuthStruct->userId;
        $_SERVER[TokenAuthService::USERNAME_HEADER] = $tokenAuthStruct->username;

        // 4. 后续请求
        return $next($request);
    }
}
