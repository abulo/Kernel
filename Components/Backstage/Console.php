<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 17-10-30
 * Time: 下午7:25
 */

namespace Kernel\Components\Backstage;

use Kernel\Components\CatCache\CatCacheRpcProxy;
use Kernel\Components\Cluster\ClusterProcess;
use Kernel\Components\Process\ProcessManager;
use Kernel\Components\SDHelp\SDHelpProcess;
use Kernel\CoreBase\Actor;
use Kernel\CoreBase\Controller;
use Kernel\Start;
use Kernel\SwooleMarco;

class Console extends Controller
{
    /**
     * onConnect
     * @return \Generator
     */
    public function back_onConnect()
    {
        $this->bindUid("#bs:" . getNodeName() . $this->fd);
        getInstance()->protect($this->fd);
        $this->addSub('$SYS/#');
    }

    /**
     * onClose
     */
    public function back_onClose()
    {

    }

    /**
     * 设置debug
     * @param $node_name
     * @param $bool
     */
    public function back_setDebug($node_name, $bool)
    {
        if (getInstance()->isCluster()) {
            ProcessManager::getInstance()->getRpcCall(ClusterProcess::class, true)->my_setDebug($node_name, $bool);
        } else {
            Start::setDebug($bool);
        }
        $this->autoSend("ok");
    }

    /**
     * reload
     * @param $node_name
     */
    public function back_reload($node_name)
    {
        if (getInstance()->isCluster()) {
            ProcessManager::getInstance()->getRpcCall(ClusterProcess::class, true)->my_reload($node_name);
        } else {
            getInstance()->server->reload();
        }
        $this->autoSend("ok");
    }

    /**
     * 获取所有的Sub
     */
    public function back_getAllSub()
    {
        $result = ProcessManager::getInstance()->getRpcCall(ClusterProcess::class)->my_getAllSub();
        $this->autoSend($result);
    }

    /**获取uid信息
     * @param $uid
     */
    public function back_getUidInfo($uid)
    {
        $uidInfo = getInstance()->getUidInfo($uid);
        $this->autoSend($uidInfo);
    }

    /**
     * 获取所有的uid
     */
    public function back_getAllUids()
    {
        $uids = getInstance()->coroutineGetAllUids();
        $this->autoSend($uids);
    }

    /**
     * 获取sub的uid
     * @param $topic
     */
    public function back_getSubUid($topic)
    {
        $uids = getInstance()->getSubMembersCoroutine($topic);
        $this->autoSend($uids);
    }

    /**
     * 获取uid所有的订阅
     * @param $uid
     */
    public function back_getUidTopics($uid)
    {
        $topics = getInstance()->getUidTopicsCoroutine($uid);
        $this->autoSend($topics);
    }

    /**
     * 获取统计信息
     * @param $node_name
     * @param $index
     * @param $num
     */
    public function back_getStatistics($node_name, $index, $num)
    {
        if (!getInstance()->isCluster() || $node_name == getNodeName()) {
            $map = ProcessManager::getInstance()->getRpcCall(SDHelpProcess::class)->getStatistics($index, $num);
        } else {
            $map = ProcessManager::getInstance()->getRpcCall(ClusterProcess::class)->my_getStatistics($node_name, $index, $num);
        }
        $this->autoSend($map);
    }

    /**
     * 获取CatCache信息
     * @param $path
     */
    public function back_getCatCacheKeys($path)
    {
        $result = CatCacheRpcProxy::getRpc()->getKeys($path);
        $this->autoSend($result);
    }

    /**
     * 获取CatCache信息
     * @param $path
     */
    public function back_getCatCacheValue($path)
    {
        $result = CatCacheRpcProxy::getRpc()[$path];
        $this->autoSend($result);
    }

    /**
     * 删除CatCache信息
     * @param $path
     */
    public function back_delCatCache($path)
    {
        unset(CatCacheRpcProxy::getRpc()[$path]);
        $this->autoSend("ok");
    }

    /**
     * 获取Actor信息
     * @param $name
     */
    public function back_getActorInfo($name)
    {
        $result = CatCacheRpcProxy::getRpc()["@Actor.$name"];
        $this->autoSend($result);
    }

    /**
     * 销毁Actor
     * @param $name
     */
    public function back_destroyActor($name)
    {
        Actor::destroyActor($name);
        $this->autoSend("ok");
    }

    /**
     * 销毁全部Actor
     */
    public function back_destroyAllActor()
    {
        Actor::destroyAllActor();
        $this->autoSend("ok");
    }

    /**
     * @param $data
     */
    protected function autoSend($data)
    {
        if (is_array($data) || is_object($data)) {
            $output = json_encode($data, JSON_UNESCAPED_UNICODE);
        } else {
            $output = $data;
        }
        switch ($this->request_type) {
            case SwooleMarco::TCP_REQUEST:
                $this->send($output);
                break;
            case SwooleMarco::HTTP_REQUEST:
                $this->http_output->setHeader("Access-Control-Allow-Origin", "*");
                $this->http_output->end($output);
                break;
        }
    }
}
