<?php
namespace Kernel\Utilities\Aria2;

class SimpleAria2JsonRpcClient extends SimpleJsonRpcClient
{
    protected $token;
    public function __construct(string $url, string $token = null)
    {
        parent::__construct($url);
        $this->token = $token;
    }
    public function call(string $name, ...$args)
    {
        if ($this->token) {
            array_unshift($args, "token:{$this->token}");
        }
        return parent::call("aria2.{$name}", ...$args);
    }
    public function stsyemCall(string $name, ...$args)
    {
        return parent::call("system.{$name}", ...$args);
    }
}
