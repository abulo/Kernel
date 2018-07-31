<?php
/**
 * Created by PhpStorm.
 * User: abulo
 * Date: 17-10-30
 * Time: 下午7:25
 */

namespace Kernel\Components\Backstage;

use Kernel\Components\Cluster\ClusterProcess;
use Kernel\Components\Process\ProcessManager;
use Kernel\CoreBase\ChildProxy;
use Kernel\CoreBase\Model;
use Kernel\Start;

class ConsoleModel extends Model
{
    protected $enable = false;
    protected $websocket_port = false;

    public function __construct(string $proxy = ChildProxy::class)
    {
        parent::__construct($proxy);
        $this->enable = $this->config->get("backstage.enable");
        $this->websocket_port = $this->config->get("backstage.websocket_port");
    }

    /**
     * 获取Node状态
     * @return void
     * @throws \Kernel\Asyn\MQTT\Exception
     * @throws \Exception
     */
    public function getNodeStatus()
    {
        $port = getInstance()->getPort($this->websocket_port);
        if (count($port->connections) == 0) {
            return;
        }
        if (Start::isLeader() && $this->enable) {
            $status["isCluster"] = getInstance()->isCluster();
            if (getInstance()->isCluster()) {
                ProcessManager::getInstance()->getRpcCall(ClusterProcess::class, true)->my_status();
                $nodes = ProcessManager::getInstance()->getRpcCall(ClusterProcess::class)->getNodes();
                sort($nodes);
                $status["nodes"] = $nodes;
            } else {
                $status["nodes"] = [getNodeName()];
                getInstance()->getStatus();
            }
            getInstance()->pub('$SYS/status', $status);
        }
    }
}
