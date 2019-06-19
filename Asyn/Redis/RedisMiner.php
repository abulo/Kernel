<?php


namespace Kernel\Asyn\Redis;

use Kernel\CoreBase\Child;
use Kernel\Memory\Pool;
use Kernel\CoreBase\SwooleException;

class RedisMiner extends Child
{


    /**
     * @var RedisAsynPool
     */
    public $redis_pool;
    /**
     * @var array
     */
    protected $activeConfig;
    /**
     * Redis database connection to use in executing the statement.
     *
     * @var \Redis|null
     */
    private $RedisConnection;

    /**
     * Miner constructor.
     * @param $redis_pool
     */
    public function __construct($redis_pool = null)
    {
        parent::__construct();
        $this->redis_pool = $redis_pool;
    }


        /**
     * @var
     */
    protected $client;

    /**
     * 事务用的client
     * @param $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }

    /**
     * @param $redis_pool
     * @return $this
     */
    public function setPool(RedisAsynPool $redis_pool)
    {
        $this->core_name = $redis_pool->getAsynName();
        $this->redis_pool = $redis_pool;
        $this->clear();
        return $this;
    }



    /**
     * 变量清楚
     *
     * @return void
     */
    public function clear()
    {
    }


    /**
     * Get the Redis database connection to use in executing this statement.
     *
     * @return \Redis|null
     */
    public function getRedisConnection()
    {
        return $this->RedisConnection;
    }

    /**
     * Set the Redis database connection to use in executing this statement.
     *
     * @param  \Redis|null $RedisConnection optional Redis database connection
     * @return Miner
     */
    public function setRedisConnection(\Redis $RedisConnection = null)
    {
        $this->RedisConnection = $RedisConnection;

        return $this;
    }


    /**
     * redis连接
     * @param $activeConfig
     */
    public function redisConnect($activeConfig)
    {
        $this->activeConfig = $activeConfig;
        $redis = new \Redis();
        if ($redis->connect($activeConfig['ip'], $activeConfig['port']) == false) {
            throw new SwooleException($redis->getLastError());
            $redis = null;
        }
        if (isset($activeConfig['password']) && $activeConfig['password']) {//存在验证
            if ($redis->auth($activeConfig['password']) == false) {
                throw new SwooleException($redis->getLastError());
                $redis = null;
            }
        }
        if (isset($activeConfig['select']) && $activeConfig['select']) {
            $redis->select($activeConfig['select']);
        }
        $this->setRedisConnection($redis);
    }


    /**
     * redis 方法映射
     *
     * @param string $name
     * @param mixed $arguments
     * @return void
     */
    public function __call($name, $arguments)
    {
        $data = [
            $name,
            $arguments
        ];
        return $this->execute($data);
    }

    /**
     * @param null $sql
     * @param callable|null $set
     * @return RedisSyncHelp
     * @throws \Kernel\CoreBase\SwooleException
     * @throws \Throwable
     */
    public function execute($data)
    {
        return $this->getProxy()->_execute($data);
    }

    /**
     * 同步执行数据
     *
     * @param  $param
     * @return void
     */
    public function redisExecute($param)
    {
        //查看服务器是否链接,没有链接,则重新链接
        if (!$this->RedisConnection->isConnected()) {
            $this->redisConnect($this->activeConfig);
        }
        $name = strtolower($param[0]);
        $arguments = $param[1];
        $data = [];
        // $data['insert_id'] = 0;
        // $data['affected_rows'] = 0;
        $result = $this->RedisConnection->$name(...$arguments);
        $this->RedisConnection->close();
        if (!$result) {
            $data['result'] = false;
        } else {
            $data['result'] = $result;
        }
        return $data;
    }
    /**
     * @param null $sql
     * @param callable|null $set
     * @return RedisSyncHelp
     * @throws \Throwable
     */
    public function _execute($redisdata)
    {
        $redisCoroutine = Pool::getInstance()->get(RedisCoroutine::class);
        if (getInstance()->isTaskWorker()) {//如果是task进程自动转换为同步模式
            $this->clear();
            $data = $this->redis_pool->getSync()->redisExecute($redisdata);
            return (new RedisSyncHelp($redisdata, $data))->getRedisResult();
        } else {
            $redisCoroutine->setRequest($redisdata);
            $this->clear();
            $result = $this->redis_pool->execute($redisdata, $redisCoroutine);
            return $result;
        }
    }




    public function destroy()
    {
        parent::destroy();
        $this->clear();
        $this->client = null;
        Pool::getInstance()->push($this);
    }
}
