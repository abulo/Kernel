<?php


namespace Kernel\Asyn\Redis;

use ArrayAccess;

class RedisSyncHelp implements ArrayAccess
{
    private $elements;
    private $redis;

    public function __construct($redis, $data)
    {
        $this->redis = $redis;
        $this->elements = $data;
    }

    /**
     * 获取结果
     * @return mixed
     */
    public function getResult()
    {
        return $this->elements;
    }

    /**
     * 延迟收包
     */
    public function recv()
    {
        if (isset($this->elements["delay_recv_fuc"])) {
            $this->elements = $this->elements["delay_recv_fuc"]();
        }
    }

    public function dump()
    {
        secho("REDIS", $this->redis);
        return $this;
    }

    public function offsetExists($offset)
    {
        return isset($this->elements[$offset]);
    }

    public function offsetSet($offset, $value)
    {
        $this->elements[$offset] = $value;
    }

    public function offsetGet($offset)
    {
        return $this->elements[$offset];
    }

    public function offsetUnset($offset)
    {
        unset($this->elements[$offset]);
    }

    /**
     * @return mixed
     */
    public function result_array()
    {
        return $this->elements['result'];
    }

    /**
     *
     * @return void
     */
    public function getRedisResult()
    {
        return $this->result_array();
    }
}
