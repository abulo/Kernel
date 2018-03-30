<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 17-3-9
 * Time: 上午11:29
 */

namespace Kernel\Controllers;

use Kernel\Components\Cluster\ClusterProcess;
use Kernel\Components\Consul\ConsulHelp;
use Kernel\Components\Process\ProcessManager;
use Kernel\Components\SDHelp\SDHelpProcess;
use Kernel\CoreBase\Controller;

/**
 * SD状态控制器
 * 返回SD的运行状态
 * Class StatusController
 * @package Kernel\Controllers
 */
class Status extends Controller
{

    public function defaultMethod()
    {
        $status = getInstance()->server->stats();
        $status['now_task'] = getInstance()->getServerAllTaskMessage();
        if ($this->config['consul']['enable']) {
            $data = ProcessManager::getInstance()->getRpcCall(SDHelpProcess::class)->getData(ConsulHelp::DISPATCH_KEY);
            if (!empty($data)) {
                foreach ($data as $key => $value) {
                    $data[$key] = json_decode($value, true);
                    foreach ($data[$key] as &$one) {
                        $one = $one['Service'];
                    }
                }
            }
            $status['consul_services'] = $data;
            if ($this->config['cluster']['enable']) {
                $data = ProcessManager::getInstance()->getRpcCall(ClusterProcess::class)->getStatus();
                $status['cluster_nodes'] = $data['nodes'];
                $status['uidOnlineCount'] = $data['count'];
            }
        }
        $this->http_output->end($status);
    }
}
