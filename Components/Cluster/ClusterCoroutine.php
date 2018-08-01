<?php
/**
 * Created by PhpStorm.
 * User: abulo
 * Date: 16-9-1
 * Time: 下午4:25
 */

namespace Kernel\Components\Cluster;

use Kernel\Coroutine\CoroutineBase;
use Kernel\Memory\Pool;
use Kernel\Start;

class ClusterCoroutine extends CoroutineBase
{

    public $eventType;
    public $receive_call;
    public $token;

    public function __construct()
    {
        parent::__construct();
    }

    public function init($token, &$receive_call, $set)
    {
        $this->receive_call = $receive_call;
        $this->token = $token;
        $this->request = "[ClusterRPC]$token";
        if (Start::getDebug()) {
            secho("CLUSTER", $token);
        }
        $this->set($set);
        $receive_call[$token] = function ($data) {
            $this->coPush($data);
        };
        return $this->returnInit();
    }

    public function destroy()
    {
        Pool::getInstance()->push($this);
        unset($this->receive_call[$this->token]);
        parent::destroy();
    }

    public function send($callback)
    {
        // TODO: Implement send() method.
    }
}
