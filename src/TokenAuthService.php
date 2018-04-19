<?php
/**
 * TokenAuthService.php
 */
namespace Uniondrug\TokenAuthMiddleware;

use Phalcon\Http\RequestInterface;
use Uniondrug\Framework\Services\Service;

class TokenAuthService extends Service
{
    /**
     * Token缓存前缀
     */
    const CACHE_PREFIX = '_TOKEN_AUTH_MIDDLEWARE_';
    /**
     * UserId 头名称
     */
    const USERID_HEADER = 'HTTP_X_USERID';
    /**
     * Username 头名称
     */
    const USERNAME_HEADER = 'HTTP_X_USERNAME';
    /**
     * @var array
     */
    protected $whiteList = null;

    /**
     * 从请求信息中获取Token. 来源包括请求头，QueryString。不允许将Token放在JSON的body里面。
     * @param \Phalcon\Http\RequestInterface $request
     * @return null|string
     */
    public function getTokenFromRequest(RequestInterface $request)
    {
        $token = null;
        $authHeader = $request->getHeader('Authorization');
        if (!empty($authHeader) && preg_match("/^Bearer\s+([_a-zA-Z0-9\-]+)$/", $authHeader, $matches)) {
            $token = $matches[1];
        } else {
            $token = $request->getQuery("token", "string", null);
            if (!empty($token)) {
                unset($_GET['token']);
            } else {
                $token = $request->getPost('token', 'string', null);
                if (!empty($token)) {
                    unset($_POST['token']);
                }
            }
        }
        return $token;
    }

    /**
     * 检查URL是否在白名单中
     * @param string $uri
     * @return bool
     */
    public function isWhiteList($uri)
    {
        if (($whitelist = $this->getWhiteList()) !== '') {
            $uri = preg_replace("/\?(\S*)/", "", $uri);
            return preg_match("/^(".$whitelist.")/", $uri) > 0;
        }
        return false;
    }

    /**
     * 读取白名单的Regexp过滤规则
     * @return string
     */
    public function getWhiteList()
    {
        // 1. with last execute
        if ($this->whiteList !== null) {
            return $this->whiteList;
        }
        // 2. calc
        $config = $this->config->path('middleware.token.whitelist');
        $whiteList = '';
        if ($config instanceof Config) {
            $whiteList = preg_replace([
                "/\//",
                "/\./"
            ], [
                "\\/",
                "\\."
            ], implode('|', $config->toArray()));
        }
        $this->whiteList = $whiteList;
        return $this->whiteList;
    }

    /**
     * 检查Token是否存在。只在缓存中检查，如果存在，按照token的ttl，重新设置缓存的ttl。
     * @param string $tokenKey
     * @return false|TokenAuthStruct
     */
    public function checkToken($tokenKey)
    {
        if ($tokenStruct = $this->get($tokenKey)) {
            $this->set($tokenStruct);
            return $tokenStruct;
        }
        return false;
    }

    /**
     * 注销一个Token
     * @param $tokenKey
     */
    public function revokeToken($tokenKey)
    {
        if ($token = $this->get($tokenKey)) {
            $this->del($tokenKey);
            $this->di->getLogger('middleware')->debug(sprintf("[TokenAuth] 注销TOKEN: token=%s, userId=%d, userName=%s", $token->name, $token->userId, $token->username));
        }
    }

    /**
     * 生成Token，同时放入缓存
     * @param null $userId
     * @param null $username
     * @return string
     */
    public function issueToken($userId = null, $username = null)
    {
        if ($userId == null && $username == null) {
            throw new \RuntimeException("userId and username cannot be null at the same time", 30000);
        }
        $tokenKey = $this->security->getRandom()->uuid();
        $tokenTtl = $this->config->path('middleware.token.ttl', 7 * 86400);
        $tokenAuthStruct = TokenAuthStruct::factory([
            'name' => $tokenKey,
            'userId' => $userId,
            'username' => $username,
            'ttl' => $tokenTtl,
        ]);
        if ($this->set($tokenAuthStruct)) {
            $this->di->getLogger('middleware')->debug(sprintf("[TokenAuth] 颁发Token: token=%s, userId=%s, userName=%s", $tokenKey, $userId, $username));
            return $tokenKey;
        }
        $this->di->getLogger('middleware')->debug(sprintf("[TokenAuth] 颁发Token失败: userId=%d, userName=%s", $userId, $username));
        throw new \RuntimeException("颁发Token失败");
    }

    /**
     * 返回UserId
     * @return string
     */
    public function getUserId()
    {
        return $this->request->getHeader('x-userid');
    }

    /**
     * 返回username
     * @return string
     */
    public function getUsername()
    {
        return $this->request->getHeader('x-username');
    }

    /**
     * 缓存：根据Token串获取结构体
     * @param string $tokenKey
     * @return TokenAuthStruct|false
     */
    protected function get(string $tokenKey)
    {
        $cacheKey = static::CACHE_PREFIX.$tokenKey;
        if ($cacheValue = $this->cache->get($cacheKey)) {
            return TokenAuthStruct::factory($cacheValue);
        }
        return false;
    }

    /**
     * 缓存：保存Token
     * @param \Uniondrug\TokenAuthMiddleware\TokenAuthStruct $tokenAuthStruct
     * @return mixed
     */
    protected function set(TokenAuthStruct $tokenAuthStruct)
    {
        $cacheKey = static::CACHE_PREFIX.$tokenAuthStruct->name;
        $cacheValue = $tokenAuthStruct->toArray();
        return $this->cache->save($cacheKey, $cacheValue, $tokenAuthStruct->ttl);
    }

    /**
     * 缓存：删除一个TOKEN
     * @param string $tokenKey
     * @return mixed
     */
    protected function del($tokenKey)
    {
        $cacheKey = static::CACHE_PREFIX.$tokenKey;
        return $this->cache->delete($cacheKey);
    }
}
