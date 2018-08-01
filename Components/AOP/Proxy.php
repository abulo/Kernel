<?php
/**
 * Created by PhpStorm.
 * User: abulo
 * Date: 17-10-12
 * Time: 下午7:17
 */

namespace Kernel\Components\AOP;

abstract class Proxy implements IProxy
{
    /**
     * @var mixed
     */
    protected $own;

    public function __construct($own)
    {
        $this->own = $own;
    }

    abstract public function beforeCall($name, $arguments = null);

    abstract public function afterCall($name, $arguments = null);

    public function __call($name, $arguments)
    {
        $this->beforeCall($name, $arguments);
        try {
            $result = sd_call_user_func_array([$this->own, $name], $arguments);
            return $result;
        } catch (\Throwable $e) {
            throw  $e;
        } finally {
            $this->afterCall($name, $arguments);
        }
    }

    public function __set($name, $value)
    {
        $this->own->$name = $value;
    }

    public function __get($name)
    {
        return $this->own->$name;
    }

    public function getOwn()
    {
        return $this->own;
    }
}
