<?php
/**
 * Created by PhpStorm.
 * User: abulo
 * Date: 18-3-8
 * Time: 下午2:36
 */

namespace Kernel\Asyn\Mysql;

use Kernel\Asyn\IAsynPool;
use Kernel\CoreBase\SwooleException;
use Kernel\Memory\Pool;

class RedisAsynPool implements IAsynPool
{
    const AsynName = 'redis';
    protected $pool_chan;
    private $active;
    protected $config;
    protected $name;
    protected $redis_client;
    private $client_max_count;



    public function __construct($config, $active)
    {
        $this->active = $active;
        $this->config = getInstance()->config;
        $this->client_max_count = $this->config->get('redis.asyn_max_count', 10);
        if (getInstance()->isTaskWorker()) {
            return;
        }
        $this->pool_chan = new \chan($this->client_max_count);
        for ($i = 0; $i < $this->client_max_count; $i++) {
            $client = new \Swoole\Coroutine\Redis();
            $client->id = $i;
            $this->pushToPool($client);
        }
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function getAsynName()
    {
        return self::AsynName . ":" . $this->name;
    }

    /**
     * 回归链接
     *
     * @param obj $client
     * @return void
     */
    public function pushToPool($client)
    {
        $this->pool_chan->push($client);
    }

    /**
     * 协程模式
     * @param $name
     * @param array ...$arg
     * @param callable $set
     * @return RedisCoroutine
     * @throws SwooleException
     */
    public function coroutineSend($name, $arg, callable $set = null)
    {
        if (getInstance()->isTaskWorker()) {//如果是task进程自动转换为同步模式
            try {
                $value = sd_call_user_func_array([$this->getSync(), $name], $arg);
            } catch (\RedisException $e) {
                $this->redis_client = null;
                $value = sd_call_user_func_array([$this->getSync(), $name], $arg);
            }
            return $value;
        } else {
            return Pool::getInstance()->get(RedisCoroutine::class)->init($this, $name, $arg, $set);
        }
    }

    /**
     * 获取同步
     * @return \Redis
     * @throws SwooleException
     */
    public function getSync()
    {
        if ($this->redis_client != null) {
            return $this->redis_client;
        }
        //同步redis连接，给task使用
        $this->redis_client = new \Redis();
        if ($this->redis_client->connect($this->config['redis'][$this->active]['ip'], $this->config['redis'][$this->active]['port']) == false) {
            throw new SwooleException($this->redis_client->getLastError());
            $this->redis_client = null;
        }
        if (!empty($this->config->get('redis.' . $this->active . '.password', ""))) {//存在验证
            if ($this->redis_client->auth($this->config['redis'][$this->active]['password']) == false) {
                throw new SwooleException($this->redis_client->getLastError());
                $this->redis_client = null;
            }
        }
        if ($this->config->has('redis.' . $this->active . '.select')) {//存在select
            $this->redis_client->select($this->config['redis'][$this->active]['select']);
        }
        return $this->redis_client;
    }


    /**
     * 协程模式 更加便捷
     * @return \Redis
     * @throws SwooleException
     */
    public function getCoroutine()
    {
        return Pool::getInstance()->get(CoroutineRedisHelp::class)->init($this);
    }


    /**
     * Undocumented function
     *
     * @param [type] $name
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}