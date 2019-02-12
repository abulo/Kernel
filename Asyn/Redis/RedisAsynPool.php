<?php


namespace Kernel\Asyn\Redis;

use Kernel\Asyn\IAsynPool;
use Kernel\CoreBase\SwooleException;
use Kernel\Memory\Pool;

class RedisAsynPool implements IAsynPool
{
    const AsynName = 'redis';
    protected $pool_chan;
    protected $redis_arr;
    private $active;
    protected $config;
    protected $name;
    /**
     * @var RedisMiner
     */
    protected $redis_client;
    private $client_max_count;

    /**
     * 构造函数
     *
     * @param array $config
     * @param string $active
     */
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
     * @return mixed
     */
    public function getActveName()
    {
        return $this->active;
    }


    /**
     * 服务协程化
     *
     * @return void
     */
    public function getCoroutine()
    {
        return Pool::getInstance()->get(RedisMiner::class)->setPool($this);
    }

    /**
     *
     * @return void
     */
    public function getAsynName()
    {
        return self::AsynName . ":" . $this->name;
    }


    /**
     * 返还链接
     *
     * @param [type] $client
     * @return void
     */
    public function pushToPool($client)
    {
        $this->pool_chan->push($client);
    }


    /**
     * @param $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }



    /**
     * 获取同步链接
     *
     * @return void
     */
    public function getSync()
    {
        if ($this->redis_client != null) {
            return $this->redis_client;
        }
        $activeConfig = $this->config['redis'][$this->active];
        $this->redis_client = new RedisMiner();
        $this->redis_client->pdoConnect($activeConfig);
        return $this->redis_client;
    }





    /**
     * @param $param
     * @param $client
     * @param RedisCoroutine $redisCoroutine
     * @return mixed
     * @throws \Throwable
     */
    public function execute($param, RedisCoroutine $redisCoroutine)
    {

        $name = strtolower($param[0]);
        // unset($param[0]);
        $arguments = $param[1];
        $d = "[$name ".implode(" ", $arguments)."]";
        $request = "[redis]$d";

        $notPush = false;
        $delayRecv = $redisCoroutine->getDelayRecv();
        $client = $this->pool_chan->pop();
        $client->setDefer($delayRecv);
        //判断是否都链接redis服务器
        if (!$client->connected) {
            $result = $client->connect(
                $this->config['redis'][$this->active]['ip'],
                $this->config['redis'][$this->active]['port']
            );

            if (!$result) {
                $this->pushToPool($client);
                $redisCoroutine->getResult(new SwooleException("[request]:$request,[errCode]:$client->errCode,[errMsg]:$client->errMsg"));
            }
            //密码验证
            if (!empty($this->config->get('redis.' . $this->active . '.password', ""))) {
                if (!$client->auth($this->config['redis'][$this->active]['password'])) {
                    $this->pushToPool($client);
                    $redisCoroutine->getResult(new SwooleException("[request]:$request,[errCode]:$client->errCode,[errMsg]:$client->errMsg"));
                }
            }
            //数据库选择
            if ($this->config->has('redis.' . $this->active . '.select')) {
                if (!$client->select($this->config['redis'][$this->active]['select'])) {
                    $this->pushToPool($client);
                    $redisCoroutine->getResult(new SwooleException("[request]:$request,[errCode]:$client->errCode,[errMsg]:$client->errMsg"));
                }
            }
        }

        //执行redis命令
        $res = $client->$name(...$arguments);
        if ($client->errCode) {
            $this->pushToPool($client);
            $redisCoroutine->getResult(new SwooleException("[request]:$request,[errCode]:$client->errCode,[errMsg]:$client->errMsg"));
        }
        $redisCoroutine->destroy();
        if ($delayRecv) {//延迟收包
            $data['delay_recv_fuc'] = function () use ($client) {
                $res = $client->recv();
                $data['result'] = $res;
                $data['affected_rows'] = 0;
                $data['insert_id'] = 0;
                $data['client_id'] = $client->id;
                $this->pushToPool($client);
                return $data;
            };
            return (new RedisSyncHelp($param, $data))->getRedisResult();
        }
        $data['result'] = $res;
        $data['affected_rows'] = 0;
        $data['insert_id'] = 0;
        $data['client_id'] = $client->id;
        if (!$notPush) {
            $this->pushToPool($client);
        }
        return (new RedisSyncHelp($param, $data))->getRedisResult();
    }
}
