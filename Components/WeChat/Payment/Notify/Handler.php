<?php

namespace Kernel\Components\WeChat\Payment\Notify;

use Kernel\Components\WeChat\Kernel\Support\Util;
use Kernel\Components\WeChat\Kernel\Support\XML;
use Kernel\Components\WeChat\Payment\Application;

/**
 * Class Handler
 *
 * @package \Kernel\Components\WeChat\Payment\Notify
 */
abstract class Handler
{
    const SUCCESS = 'SUCCESS';
    const FAIL = 'FAIL';

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var array
     */
    protected $message;

    /**
     * @var string|null
     */
    protected $fail;

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * Check sign.
     * If failed, throws an exception.
     *
     * @var bool
     */
    protected $check = true;

    /**
     * Respond with sign.
     *
     * @var bool
     */
    protected $sign = false;

    public function __construct($app)
    {
        $this->app = $app;
    }

    abstract public function handle(\Closure $closure);

    public function fail(string $message)
    {
        $this->fail = $message;
    }

    public function respondWith(array $attributes, bool $sign = false)
    {
        $this->attributes = $attributes;
        $this->sign = $sign;

        return $this;
    }

    public function toResponseData()
    {
        $base = [
            'return_code' => is_null($this->fail) ? static::SUCCESS : static::FAIL,
            'return_msg' => $this->fail,
        ];

        $attributes = array_merge($base, $this->attributes);

        if ($this->sign) {
            $attributes['sign'] = Util::GenerateSign($attributes, $this->app->getKey());
        }

        return XML::build($attributes);
    }

    public function getMessage()
    {
        if (!empty($this->message)) {
            return $this->message;
        }

        $message = XML::parse(strval($this->app->getHttpInput()->getRawContent()));

        if (!is_array($message) || empty($message)) {
            throw new \Exception('Invalid request XML.', 400);
        }

        if ($this->check) {
            $this->validate($message);
        }

        return $this->message = $message;
    }

    protected function validate(array $message)
    {
        $sign = $message['sign'] ?? null;
        unset($message['sign']);

        if (Util::GenerateSign($message, $this->app->getKey()) !== $sign) {
            throw new \Exception('Invalid Sign.', 400);
        }
    }

    protected function strict($result)
    {
        if (true !== $result && is_null($this->fail)) {
            $this->fail(strval($result));
        }
    }
}
