<?php
/**
 * Created by PhpStorm.
 * User: abulo
 * Date: 17-9-28
 * Time: 下午2:49
 */

namespace Kernel\Components\Middleware;

abstract class TcpMiddleware extends Middleware
{
    protected $fd;
    protected $client_data;

    public function init($fd, $client_data)
    {
        $this->fd = $fd;
        $this->client_data = $client_data;
    }

    /**
     * sendToUid
     * @param $data
     */
    protected function send($data)
    {
        getInstance()->send($this->fd, $data, true);
    }

    protected function close()
    {
        getInstance()->close($this->fd);
        throw new \Exception('close');
    }
}
