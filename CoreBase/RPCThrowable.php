<?php
/**
 * Created by PhpStorm.
 * User: abulo
 * Date: 18-3-15
 * Time: 下午3:16
 */

namespace Kernel\CoreBase;

class RPCThrowable
{
    public $className;
    public $message;
    public $code;

    public function __construct(\Throwable $e)
    {
        $this->message = $e->getMessage();
        $this->code = $e->getCode();
        $this->className = get_class($e);
    }

    public function build()
    {
        return new $this->className($this->message, $this->code);
    }
}
