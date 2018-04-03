<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 17-3-29
 * Time: 下午1:47
 */

namespace Kernel\Asyn\Mysql;

/**
 * Redis lua 脚本管理器
 * Class RedisLuaManager
 * @package Kernel\Asyn\Redis
 */
class MysqlProxy
{
    public $proxy_config;
    public function __construct($proxy_config)
    {
        $this->proxy_config = $proxy_config;
    }

    public function master()
    {
        $master = $this->proxy_config['master'];
        if(getInstance()->isTaskWorker())
        {
            return getInstance()->getMysql($master);
        }else{
            return getInstance()->getMysqlPool($master)->installDbBuilder();
        }

    }

    public function slave()
    {
        $slave = $this->proxy_config['slave'];
        //在Swoole中如果在父进程内调用了mt_rand，不同的子进程内再调用mt_rand返回的结果会是相同的。所以必须在每个子进程内调用mt_srand重新播种。
        mt_srand();
        $n = mt_rand(0, count($slave) - 1);
        if(getInstance()->isTaskWorker())
        {
            return getInstance()->getMysql($slave[$n]);
        }else{
            return getInstance()->getMysqlPool($slave[$n])->installDbBuilder();
        }

    }
}
