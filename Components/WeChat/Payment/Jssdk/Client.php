<?php

namespace Kernel\Components\WeChat\Payment\Jssdk;

use Kernel\Components\WeChat\Kernel\Support\Util;
use Kernel\Components\WeChat\Payment\Kernel\BaseClient;

/**
 * Class Client
 *
 * @package \Kernel\Components\WeChat\Payment\Jssdk
 */
class Client extends BaseClient
{
    public function bridgeConfig(string $prepayId, bool $json = true)
    {
        $params = [
            'appId' => $this->app->getConfig('sub_appid') ?: $this->app->getConfig('appid'),
            'timeStamp' => strval(time()),
            'nonceStr' => uniqid(),
            'package' => "prepay_id=$prepayId",
            'signType' => 'MD5',
        ];

        $params['paySign'] = Util::GenerateSign($params, $this->app->getConfig('key'), 'md5');

        return $json ? json_encode($params) : $params;
    }
}
