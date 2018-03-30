<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 16-7-15
 * Time: 下午1:24
 */

namespace Kernel\CoreBase;

use Monolog\Logger;
use Noodlehaus\Config;
use Kernel\Asyn\Mysql\MysqlAsynPool;
use Kernel\Asyn\Redis\RedisRoute;
use Kernel\Memory\Pool;

class CoreBase extends Child
{
    /**
     * 销毁标志
     * @var bool
     */
    public $is_destroy = false;

    /**
     * @var Loader
     */
    public $loader;
    /**
     * @var Logger
     */
    public $logger;
    /**
     * @var swoole_server
     */
    public $server;
    /**
     * @var Config
     */
    public $config;
    /**
     * @var RedisRoute
     */
    // public $redis_pool;
    /**
     * @var MysqlAsynPool
     */
    // public $mysql_pool;

    protected $dbQueryBuilders = [];

    /**
     * Task constructor.
     * @param string $proxy
     */
    public function __construct($proxy = ChildProxy::class)
    {
        parent::__construct($proxy);
        if (!empty(getInstance())) {
            $this->loader = getInstance()->loader;
            $this->logger = getInstance()->log;
            $this->server = getInstance()->server;
            $this->config = getInstance()->config;
            // $this->redis_pool = RedisRoute::getInstance();
            // $this->mysql_pool = getInstance()->getAsynPool('mysqlPool');
        }
    }

    /**
     * 安装MysqlPool
     * @param MysqlAsynPool $mysqlPool
     */
    protected function installMysqlPool(MysqlAsynPool $mysqlPool)
    {
        $this->dbQueryBuilders[$mysqlPool->getActveName()] = $mysqlPool->installDbBuilder();
    }


    /**
     * 获取一个 redis 连接池
     * @param
     * @return
     */
    public function getRedisPool($name)
    {
        return getInstance()->getRedisPool($name);
    }
    /**
     * 获取 mysql pool
     * @param   $name
     * @return
     */
    public function getMysqlPool($name)
    {
        return getInstance()->getMysqlPool($name);
    }

    /**
     * 获取一个代理链接
     * @param
     * @return mysql
     */
    public function getMysqlProxy($name)
    {
        return getInstance()->getMysqlProxy($name);
    }

    /**
     * 获取 redis 代理
     * @param    $name
     * @return
     */
    public function getRedisProxy($name)
    {
        return getInstance()->getRedisProxy($name);
    }


    /**
     * 销毁，解除引用
     */
    public function destroy()
    {
        parent::destroy();
        $this->is_destroy = true;
        foreach ($this->dbQueryBuilders as $dbQueryBuilder) {
            $dbQueryBuilder->clear();
            Pool::getInstance()->push($dbQueryBuilder);
        }
        $this->dbQueryBuilders = [];
    }

    /**
     * 对象复用
     */
    public function reUse()
    {
        $this->is_destroy = false;
    }

    /**
     * 打印日志
     * @param $message
     * @param int $level
     */
    protected function log($message, $level = Logger::DEBUG)
    {
        try {
            $this->logger->addRecord($level, $message, $this->getContext());
        } catch (\Exception $e) {
        }
    }
}
