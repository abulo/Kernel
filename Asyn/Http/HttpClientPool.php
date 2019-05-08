<?php


namespace Kernel\Asyn\Http;

use Kernel\Asyn\IAsynPool;
use Kernel\CoreBase\SwooleException;
use Kernel\Memory\Pool;

class HttpClientPool implements IAsynPool
{
    const AsynName = 'http';
    protected $pool_chan;
    protected $http_arr;
    public $active;
    protected $config;
    protected $name;
    /**
     * @var RedisMiner
     */
    protected $redis_client;
    private $client_max_count;

    /**
     * 构造函数
     *
     * @param array $config
     * @param string $active
     */
    public function __construct($config, $active)
    {

        //链接地址


        $this->config = $config;
        $this->client_max_count = $this->config->get('httpClient.asyn_max_count', 10);
        if (getInstance()->isTaskWorker()) {
            return;
        }


        $arr = parse_url($active);
        $scheme = $arr['scheme'];
        $host = $arr['host'];
        if ($scheme == "https") {
            $ssl = true;
            $port = 443;
        } else {
            $ssl = false;
            $port = 80;
        }
        if (array_key_exists('port', $arr)) {
            $port  = $arr['port'];
        }

        $this->active = $scheme . $host . $port;

        $this->pool_chan = new \chan($this->client_max_count);
        for ($i = 0; $i < $this->client_max_count; $i++) {
            $client = new \Swoole\Coroutine\Http\Client($host, $port, $ssl);
            $client->id = $i;
            $this->pushToPool($client);
        }
    }



    /**
     * @return mixed
     */
    public function getActveName()
    {
        return $this->active;
    }


    /**
     * 服务协程化
     *
     * @return void
     */
    public function getCoroutine()
    {
        return Pool::getInstance()->get(HttpClientRequest::class)->setPool($this);
    }

    /**
     *
     * @return void
     */
    public function getAsynName()
    {
        return self::AsynName . ":" . $this->name;
    }


    /**
     * 返还链接
     *
     * @param [type] $client
     * @return void
     */
    public function pushToPool($client)
    {
        $this->pool_chan->push($client);
    }


    /**
     * @param $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }



    /**
     * 获取同步链接
     *
     * @return void
     */
    public function getSync()
    {
        throw new SwooleException('暂时没有HttpClientPool的同步方法');
        // if ($this->redis_client != null) {
        //     return $this->redis_client;
        // }
        // $activeConfig = $this->config['redis'][$this->active];
        // $this->redis_client = new RedisMiner();
        // $this->redis_client->redisConnect($activeConfig);
        // return $this->redis_client;
    }





    /**
     * @param $param
     * @param $client
     * @param HttpClientCoroutine $httpClientCoroutine
     * @return mixed
     * @throws \Throwable
     */
    public function execute($param, HttpClientCoroutine $httpClientCoroutine)
    {

        //获取一个链接
        $client = $this->pool_chan->pop();

        /** 设定配置项 */

        $settings = [
            'timeout' => isset($param['set']['timeout']) ? $param['set']['timeout'] : 3,
            'keep_alive' => isset($param['set']['keep_alive']) ? $param['set']['keep_alive'] : false,
        ];

        $client->set($settings);


        //设置setMethod
        if (isset($param['setMethod'])) {
            $client->setMethod($param['setMethod']);
        }

        $headers = [];
        //设置setHeaders
        if (isset($param['setHeaders'])) {
            $headers = $param['setHeaders'];
            // $client->setHeaders($param['setHeaders']);
        }


        //设置setCookies
        $cookies = [];
        if (isset($param['setCookies'])) {
            // $client->setCookies($param['setCookies']);
            $cookies = $param['setCookies'];
        }

        $content_type = isset($headers['Content-Type']) ? $headers['Content-Type'] : '';

        if (isset($param['setData'])) {
            switch ($content_type) {
                case ContentType::get('json'):
                    $client->setData(DataParser::toJsonString($param['setData']));
                    break;
                case ContentType::get('xml'):
                    $client->setData(DataParser::toXmlString($param['setData']));
                    break;
                case ContentType::get('multipart'):
                    $boundary = '----WebKitFormBoundary' . openssl_random_pseudo_bytes(16);
                    $headers['Content-Type'] = "multipart/form-data; boundary=$boundary";
                    $client->setData(DataParser::toMultipartString($param['setData'], $boundary));
                    break;
                case ContentType::get('query'):
                default:
                    $client->setData(DataParser::toQueryString($param['setData']));
                    // $query = http_build_query($param['setData']);
            }
        }

        $query = "";
        if (isset($param['setQuery'])) {
            $query = http_build_query($param['setQuery']);
        }


        if (isset($param['addFile'])) {
            $client->addFile(...array_values($param['addFile']));
        }

        if (isset($param['addData'])) {
            $client->addData(...array_values($param['addData']));
        }
        $path = $param['exec']['path'] ?: '/';
        $parse_url = parse_url($param['exec']['path']);
        $path = $parse_url['path'] ?: '/';
        $path = '/'.ltrim($path, '/');
        if ($query) {
            $path .= '?'.$query;
        }

        $client->setHeaders($headers);
        $client->setCookies($cookies);
        $delayRecv = $httpClientCoroutine->getDelayRecv();
        $client->setDefer($delayRecv); //总是延迟回包以使用timeout定时器特性

        if (isset($param['download'])) {
            $client->requestPath = $param['download'][0];
            $client->download(...array_values($param['download']));
        } else {
            $client->requestPath = $path;
            $client->execute($path);
        }
        $httpClientCoroutine->destroy();
        if ($delayRecv) { //延迟收包
                $client->recv();
                $data['result'] = $client;
                $data['client_id'] = $client->id;
                $this->pushToPool($client);
            return (new HttpClientSyncHelp($param, $data))->getHttpResult();
        }
        $data['result'] = $client;
        $data['client_id'] = $client->id;
        $this->pushToPool($client);
        return (new HttpClientSyncHelp($param, $data))->getHttpResult();
    }
}
