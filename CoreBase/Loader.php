<?php
/**
 * Loader 加载器
 * Created by PhpStorm.
 * User: abulo
 * Date: 16-7-15
 * Time: 下午12:21
 */

namespace Kernel\CoreBase;

use Kernel\Asyn\Mysql\Miner;
use Kernel\Asyn\Mysql\MysqlAsynPool;
use Kernel\Asyn\Redis\RedisAsynPool;
use Kernel\Asyn\Http\HttpClientPool;
use Kernel\Components\AOP\AOP;
use Kernel\Memory\Pool;

class Loader implements ILoader
{
    private $_task_proxy;
    private $_model_factory;

    public function __construct()
    {
        $this->_task_proxy = new TaskProxy();
        $this->_model_factory = ModelFactory::getInstance();
    }

    /**
     * 获取一个 mongodb
     *
     * @param  $name
     * @return void
     */
    public function mongodb($name)
    {
        $mongodb = getInstance()->getMongoPool($name);
        if ($mongodb == null) {
            return null;
        }
        return $mongodb;
    }

    /**
     * 获取一个redis
     * @param $name
     * @param Child $parent
     * @return \Redis
     */
    public function redis($name, Child $parent)
    {
        if (empty($name)) {
            return null;
        }
        if ($parent->root == null) {
            $parent->root = $parent;
        }
        $root = $parent->root;
        $core_name = RedisAsynPool::AsynName . ":" .$name;
        if ($root->hasChild($core_name)) {
            return $root->getChild($core_name);
            // return AOP::getAOP($root->getChild($core_name));
        }
        $redisPool = getInstance()->getAsynPool($name);
        if ($redisPool == null) {
            return null;
        }
        $redis = $redisPool->getCoroutine();
        $redis->setContext($root->getContext());
        $root->addChild($redis);
        return $redis;
        // return AOP::getAOP($redis);
    }



    /**
     * 获取一个http
     * @param $name
     * @param Child $parent
     * @return \Http
     */
    public function http($name, Child $parent)
    {
        if (empty($name)) {
            return null;
        }
        if ($parent->root == null) {
            $parent->root = $parent;
        }
        $root = $parent->root;
        $core_name = HttpClientPool::AsynName . ":" .$name;
        if ($root->hasChild($core_name)) {
            return $root->getChild($core_name);
            // return AOP::getAOP($root->getChild($core_name));
        }
        $httpPool = getInstance()->getAsynPool($name);
        if ($httpPool == null) {
            return null;
        }
        $http = $httpPool->getCoroutine();
        $http->setContext($root->getContext());
        $root->addChild($http);
        return $http;
        // return AOP::getAOP($redis);
    }

    /**
     * 获取一个mysql
     * @param $name
     * @param Child $parent
     * @return Miner
     */
    public function mysql($name, Child $parent)
    {
        if (empty($name)) {
            return null;
        }
        if ($parent->root == null) {
            $parent->root = $parent;
        }
        $root = $parent->root;
        $core_name = MysqlAsynPool::AsynName . ":" .$name;
        if ($root->hasChild($core_name)) {
            return $root->getChild($core_name);
            // return AOP::getAOP($root->getChild($core_name));
        }
        $mysql_pool = getInstance()->getAsynPool($name);
        if ($mysql_pool == null) {
            return null;
        }
        $db = $mysql_pool->installDbBuilder();
        $db->setContext($root->getContext());
        $root->addChild($db);
        return $db;
        // return AOP::getAOP($db);
    }

    /**
     * 获取一个model
     * @param $model
     * @param Child $parent
     * @return mixed|null
     * @throws SwooleException
     */
    public function model($model, Child $parent)
    {
        if (empty($model)) {
            return null;
        }
        if ($parent->root == null) {
            $parent->root = $parent;
        }
        $root = $parent->root;
        if ($root->hasChild($model)) {
            return AOP::getAOP($root->getChild($model));
        }
        $model_instance = $this->_model_factory->getModel($model);
        $model_instance->root = $root;
        $root->addChild($model_instance);
        $model_instance->initialization($parent->getContext());
        return AOP::getAOP($model_instance);
    }

    /**
     * 获取一个task
     * @param $task
     * @param Child $parent
     * @return mixed|null|TaskProxy
     * @throws SwooleException
     */
    public function task($task, Child $parent = null)
    {
        if (empty($task)) {
            return null;
        }
        if (class_exists($task)) {
            $task_class = $task;
        } else {
            throw new SwooleException("class task_class not exists");
        }
        if (!getInstance()->server->taskworker) {//工作进程返回taskproxy
            $this->_task_proxy->core_name = $task_class;
            if ($parent != null) {
                $this->_task_proxy->setContext($parent->getContext());
            }
            return AOP::getAOP($this->_task_proxy);
        }
        $task_instance = Pool::getInstance()->get($task_class);
        $task_instance->reUse();
        return $task_instance;
    }



    /**
     * view 返回一个模板
     * @param $template
     * @return
     */
    public function view($template)
    {
        $template = getInstance()->templateEngine->make($template);
        return $template;
    }
    public function tpl($template)
    {
        return $this->view(TPL_PATH.DS.$template);
    }
}
