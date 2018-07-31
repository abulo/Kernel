<?php
/**
 * Created by PhpStorm.
 * User: abulo
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
        }
    }

    /**
     * 销毁，解除引用
     */
    public function destroy()
    {
        parent::destroy();
        $this->is_destroy = true;
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
