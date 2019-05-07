<?php


namespace Kernel\Asyn\Http;

use Kernel\CoreBase\SwooleException;
use Kernel\Coroutine\CoroutineBase;
use Kernel\Memory\Pool;
use Kernel\Start;

class HttpClientCoroutine extends CoroutineBase
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
        $name = $data['active'];
        $d = "[$name ".json_encode($data)."]";
        $this->request = "[httpClient]$d";
        if (Start::getDebug()) {
            secho("HTTP", $d);
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
