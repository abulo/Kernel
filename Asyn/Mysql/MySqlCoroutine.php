<?php
/**
 * Created by PhpStorm.
 * User: abulo
 * Date: 16-9-1
 * Time: 下午4:25
 */

namespace Kernel\Asyn\Mysql;

use Kernel\CoreBase\SwooleException;
use Kernel\Coroutine\CoroutineBase;
use Kernel\Memory\Pool;
use Kernel\Start;

class MySqlCoroutine extends CoroutineBase
{

    public function __construct()
    {
        parent::__construct();
    }

    public function send($callback)
    {
        // TODO: Implement send() method.
    }

    public function setRequest($sql)
    {
        $this->request = "[sql]$sql";
        if (Start::getDebug()) {
            secho("SQL", $sql);
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
