<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 17-10-30
 * Time: 下午7:25
 */

namespace Kernel\Components\Backstage;

use Kernel\Components\Cluster\ClusterProcess;
use Kernel\Components\Process\ProcessManager;
use Kernel\CoreBase\Model;
use Kernel\Start;

class ConsoleModel extends Model
{
    /**
     * 获取Node状态
     * @return \Generator
     */
    public function getNodeStatus()
    {
        if (Start::isLeader()) {
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
