<?php

namespace Kernel\Components\WeChat\OfficialAccount\OAuth;

use Kernel\Asyn\HttpClient\HttpClientPool;
use Kernel\Components\WeChat\OfficialAccount\Application;
use Kernel\CoreBase\Model;

/**
 * Class BaseClient
 *
 * @package \Kernel\Components\WeChat\OfficialAccount\OAuth
 */
class BaseClient extends Model
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @var HttpClientPool
     */
    private $httpClientPool;

    public function initialization(&$context)
    {
        parent::initialization($context);

        $this->app = $this->loader->model(Application::class, $this);
        $this->httpClientPool = getInstance()->getAsynPool('WeChatApi');
    }

    /**
     * @return \Kernel\Asyn\HttpClient\HttpClient
     */
    protected function getHttpClient()
    {
        return $this->httpClientPool->httpClient;
    }
}
