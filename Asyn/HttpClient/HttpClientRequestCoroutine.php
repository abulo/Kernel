<?php
/**
 * Created by PhpStorm.
 * User: abulo
 * Date: 16-9-1
 * Time: 下午4:25
 */

namespace Kernel\Asyn\HttpClient;

use Kernel\Coroutine\CoroutineBase;
use Kernel\Memory\Pool;
use Kernel\Start;

class HttpClientRequestCoroutine extends CoroutineBase
{
    /**
     * @var HttpClientPool
     */
    public $pool;
    public $data;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 对象池模式代替__construct
     * @param HttpClientPool $pool
     * @param $data
     * @param $set
     * @return $this
     */
    public function init(HttpClientPool $pool, $data, $set)
    {
        $this->pool = $pool;
        $this->data = $data;
        $d = "[".$pool->baseUrl . $data['path']."]";
        $this->request = "[httpClient]$d";
        $this->set($set);
        if (Start::getDebug()) {
            secho("HTTP", $d);
        }
        if ($this->fuse()) {//启动断路器
            $this->send(function ($result) {
                $this->coPush($result);
            });
        }
        return $this->returnInit();
    }

    public function send($callback)
    {
        $this->token = $this->pool->call($this->data, $callback);
    }

    public function destroy()
    {
        parent::destroy();
        $this->pool->removeTokenCallback($this->token);
        $this->pool = null;
        $this->data = null;
        $this->token = null;
        Pool::getInstance()->push($this);
    }

    protected function onTimerOutHandle()
    {
        parent::onTimerOutHandle();
        $this->pool->destoryGarbage($this->token);
    }
}
