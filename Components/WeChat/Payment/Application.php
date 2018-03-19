<?php

namespace Kernel\Components\WeChat\Payment;

use Kernel\Components\WeChat\Kernel\Support\Util;
use Kernel\Components\WeChat\Payment\Jssdk\Client as Jssdk;
use Kernel\Components\WeChat\Payment\Order\Client as Order;
use Kernel\CoreBase\HttpInput;
use Kernel\CoreBase\Model;

/**
 * Class Application
 *
 * @package \Kernel\Components\WeChat\Payment
 */
class Application extends Model
{
    const TRADE_TYPE_JSPAI = 'JSAPI'; //公众号支付
    const TRADE_TYPE_NATIVE = 'NATIVE'; //扫码支付
    const TRADE_TYPE_APP = 'APP'; //APP支付

    /**
     * @var array
     */
    protected $configs;

    /**
     * @var HttpInput
     */
    protected $httpInput;

    /**
     * @var Order
     */
    public $order;

    /**
     * @var Jssdk
     */
    public $jssdk;

    public function initialization(&$context)
    {
        parent::initialization($context);

        $this->order = $this->loader->model(Order::class, $this);
        $this->jssdk = $this->loader->model(Jssdk::class, $this);
    }

    /**
     * @param array $configs
     * @return $this
     */
    public function setConfigs(array $configs)
    {
        $this->configs = $configs;

        return $this;
    }

    /**
     * @param string $key
     * @param string $default
     * @return mixed
     */
    public function getConfig(string $key, $default = '')
    {
        return $this->configs[$key] ?? $default;
    }

    /**
     * @param HttpInput $httpInput
     * @return $this
     */
    public function setHttpInput(HttpInput $httpInput)
    {
        $this->httpInput = $httpInput;

        return $this;
    }

    /**
     * @return HttpInput
     */
    public function getHttpInput()
    {
        return $this->httpInput;
    }

    public function getServerIp()
    {
        if (!empty($this->getHttpInput()->header('host'))) {
            $ip = gethostbyname($this->getHttpInput()->header('host'));
        } else {
            $ip = $this->getHttpInput()->server('server_addr');
        }

        return filter_var($ip, FILTER_VALIDATE_IP) ?: '127.0.0.1';
    }

    public function getClientIp()
    {
        if (!empty($this->getHttpInput()->header('x-real-ip'))) {
            $ip = $this->getHttpInput()->header('x-real-ip');
        } else {
            $ip = $this->getHttpInput()->server('remote_addr');
        }

        return filter_var($ip, FILTER_VALIDATE_IP) ?: '127.0.0.1';
    }

    /**
     * 扫码支付（模式一）
     * @param string $productId
     * @return string
     */
    public function scheme(string $productId)
    {
        $params = [
            'appid' => $this->getConfig('appid'),
            'mch_id' => $this->getConfig('mch_id'),
            'time_stamp' => time(),
            'nonce_str' => uniqid(),
            'product_id' => $productId,
        ];

        $params['sign'] = Util::GenerateSign($params, $this->key);

        return 'weixin://wxpay/bizpayurl?'.http_build_query($params);
    }

    public function handlePaidNotify(\Closure $closure)
    {
        $result = yield (new Notify\Paid($this))->handle($closure);

        return $result;
    }

    public function inSandbox()
    {
        //TODO
        return false;
    }

    public function getKey(string $endpoint = null)
    {
        if ('sandboxnew/pay/getsignkey' === $endpoint) {
            return $this->getConfig('key');
        }

        return $this->inSandbox() ? '' : $this->getConfig('key');
    }
}
