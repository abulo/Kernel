<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 16-9-1
 * Time: 下午4:25
 */

namespace Kernel\CoreBase;

use Kernel\Coroutine\CoroutineBase;
use Kernel\Coroutine\CoroutineNull;
use Kernel\Memory\Pool;
use Kernel\Start;

class TaskCoroutine extends CoroutineBase
{
    public $id;
    public $task_proxy_data;
    public $task_id;

    public function init($task_proxy_data, $id, $set)
    {
        $this->task_proxy_data = $task_proxy_data;
        $this->id = $id;
        $this->set($set);
        $this->send(function ($serv, $task_id, $data) {
            if ($data instanceof CoroutineNull) {
                $data = null;
            }
            $this->coPush($data);
        });
        $d = "[".$task_proxy_data['message']['task_name'] ."::". $task_proxy_data['message']['task_fuc_name']."]";
        $this->request = "[Task]$d";
        if (Start::getDebug()) {
            secho("TASK", $d);
        }
        return $this->returnInit();
    }

    public function send($callback)
    {
        $this->task_id = getInstance()->server->worker_id . getInstance()->server->task($this->task_proxy_data, $this->id, $callback);
    }

    public function destroy()
    {
        parent::destroy();
        $this->task_id = null;
        Pool::getInstance()->push($this);
    }

    protected function onTimerOutHandle()
    {
        parent::onTimerOutHandle();
        getInstance()->stopTask($this->task_id);
    }
}
