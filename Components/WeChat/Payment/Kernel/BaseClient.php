<?php

namespace Kernel\Components\WeChat\Payment\Kernel;

use Kernel\Asyn\HttpClient\HttpClientPool;
use Kernel\Components\WeChat\Kernel\Http\Response;
use Kernel\Components\WeChat\Kernel\Support\Util;
use Kernel\Components\WeChat\Kernel\Support\XML;
use Kernel\Components\WeChat\Payment\Application;
use Kernel\CoreBase\Model;

/**
 * Class Base
 *
 * @package \Kernel\Components\WeChat
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
    protected $httpClientPool;

    /**
     * @var HttpClient
     */
    protected $httpClient;


    public function initialization(&$context)
    {
        parent::initialization($context);

        $this->app = $this->loader->model(Application::class, $this);
        $this->httpClientPool = getInstance()->getAsynPool('WxPayApi');
        $this->httpClient = $this->httpClientPool->httpClient;
    }

    protected function prepends()
    {
        return [];
    }

    protected function request(string $endpoint, array $params = [], $method = 'post', array $options = [], $returnResponse = false)
    {
        $base = [
            'mch_id' => $this->app->getConfig('mch_id'),
            'nonce_str' => uniqid(),
            'sub_mch_id' => $this->app->getConfig('sub_mch_id'),
            'sub_appid' => $this->app->getConfig('sub_appid'),
        ];

        $params = array_filter(array_merge($base, $this->prepends(), $params));

        $params['sign'] = Util::GenerateSign($params, $this->app->getKey());
        $options = array_merge([
            'body' => XML::build($params),
        ], $options);

        $response =  $this->performRequest($endpoint, $method, $options);

        return $returnResponse ? $response : $this->castResponseToType($response);
    }

    protected function wrap(string $endpoint)
    {
        return $this->app->inSandbox() ? "sandboxnew/{$endpoint}" : $endpoint;
    }

    protected function performRequest($url, $method = 'GET', array $options = [])
    {
        $method = strtoupper($method);

        $client = $this->httpClient;

        $client->setMethod($method);

        if (isset($options['body'])) {
            $client->setData($options['body']);
        }

        $url = '/' . ltrim($url, '/');

        $responseData =  $client->coroutineExecute($url);

        return new Response($responseData);
    }

    protected function castResponseToType(Response $response, $type = null)
    {
        switch ($type ?? 'array') {
            case 'array':
                return $response->toArray();
            case 'raw':
                return $response;
        }
    }
}
