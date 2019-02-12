<?php


namespace Kernel\Asyn\Redis;

use Kernel\CoreBase\SwooleException;
use Kernel\Coroutine\CoroutineBase;
use Kernel\Memory\Pool;
use Kernel\Start;

class RedisCoroutine extends CoroutineBase
{

    public function __construct()
    {
        parent::__construct();
    }

    public function send($callback)
    {
        // TODO: Implement send() method.
    }

    public function setRequest($data)
    {
        // secho('$data',$data);
        $name = $data[0];
        // unset($data[0]);
        $arguments = $data[1];
        $d = "[$name ".implode(" ", $arguments)."]";
        $this->request = "[redis]$d";
        if (Start::getDebug()) {
            secho("REDIS", $d);
        }
    }

    public function onTimeOut()
    {
        if (empty($this->downgrade)) {
            $result = new SwooleException("[CoroutineTask]: Time Out!, [Request]: $this->request");
        } else {
            $result = sd_call_user_func($this->downgrade);
        }
        $result = $this->getResult($result);
        return $result;
    }

    /**
     * @throws SwooleException
     */
    public function destroy()
    {
        parent::destroy();
        Pool::getInstance()->push($this);
    }
}
