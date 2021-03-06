<?php
/**
 * Created by PhpStorm.
 * User: abulo
 * Date: 17-1-4
 * Time: 上午9:38
 */

namespace Kernel\Asyn\Redis;

use Kernel\CoreBase\Child;

use Kernel\Memory\Pool;

class CoroutineRedisHelp extends Child
{
    /**
     * @var RedisAsynPool
     */
    private $redisAsynPool;

    public function init(RedisAsynPool $redisAsynPool)
    {
        $this->redisAsynPool = $redisAsynPool;
        $this->core_name = $redisAsynPool->getAsynName();
        return $this;
    }

    public function __call($name, $arguments)
    {
        if (getInstance()->isTaskWorker()) {//如果是task进程自动转换为同步模式
            return sd_call_user_func_array([$this->redisAsynPool->getSync(), $name], $arguments);
        } else {
            return Pool::getInstance()->get(RedisCoroutine::class)->init($this->redisAsynPool, $name, $arguments, null);
        }
    }
}
