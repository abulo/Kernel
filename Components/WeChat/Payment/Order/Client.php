<?php

namespace Kernel\Components\WeChat\Payment\Order;

use Kernel\Components\WeChat\Kernel\Support\GetIp;
use Kernel\Components\WeChat\Payment\Application;
use Kernel\Components\WeChat\Payment\Kernel\BaseClient;

/**
 * Class Client
 *
 * @package \Kernel\Components\WeChat\Payment\Order
 */
class Client extends BaseClient
{
    public function unify(array $params)
    {
        if (empty($params['spbill_create_ip'])) {
            $params['spbill_create_ip'] = (Application::TRADE_TYPE_NATIVE === $params['trade_type']) ? $this->app->getServerIp() : $this->app->getClientIp();
        }

        $params['appid'] = $this->app->getConfig('appid');
        $params['notify_url'] = $params['notify_url'] ?? $this->app->getConfig('notify_url');

        $response =  $this->request($this->wrap('pay/unifiedorder'), $params);
        return $response;
    }

    public function queryByOutTradeNumber(string $tradeNo)
    {
        $response =  $this->query([
            'out_trade_no' => $tradeNo,
        ]);
        return $response;
    }

    public function queryByTransactionId(string $transactionId)
    {
        $response =  $this->query([
            'transaction_id' => $transactionId,
        ]);
        return $response;
    }

    protected function query(array $params)
    {
        $params['appid'] = $this->app->getConfig('appid');

        $response =  $this->request($this->wrap('pay/orderquery'), $params);
        return $response;
    }

    public function close(string $tradeNo)
    {
        $params = [
            'appid' => $this->app->getConfig('appid'),
            'out_trade_no' => $tradeNo,
        ];

        $response = $this->request($this->wrap('pay/closeorder'), $params);
        return $response;
    }
}
