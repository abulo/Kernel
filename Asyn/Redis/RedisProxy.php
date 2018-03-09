<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 17-3-29
 * Time: 下午1:47
 */

namespace Kernel\Asyn\Redis;

/**
 * Redis lua 脚本管理器
 * Class RedisLuaManager
 * @package Kernel\Asyn\Redis
 */
class RedisProxy
{
    public $proxy_config;
    public function __construct($proxy_config)
    {
        $this->proxy_config = $proxy_config;
    }



    public function getRedisPool($arguments)
    {
        $val = $arguments[0];

        $n = sprintf('%u', crc32($val)) % count($this->proxy_config);

        $pool = $this->proxy_config[$n];
        return getInstance()->getRedisPool($pool);
    }


    public function __call($method, $arguments)
    {
        $redis_pool = $this->getRedisPool($arguments);
        return $redis_pool->getCoroutine()->$method(...$arguments);
    }
}
